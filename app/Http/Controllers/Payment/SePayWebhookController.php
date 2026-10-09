<?php

namespace App\Http\Controllers\Payment;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SePayWebhookController extends Controller
{
    /**
     * Handle incoming IPN webhook from SePay Sandbox.
     */
    public function handle(Request $request, PaymentGateway $gateway): JsonResponse
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        // 1. Verify webhook authenticity (AC-02)
        if (! $gateway->verifyWebhook($payload, $headers)) {
            Log::warning('SePay IPN verification failed: invalid token or authorization header.');

            return response()->json([
                'error' => 'Unauthorized IPN request.',
            ], 401);
        }

        // 2. Parse normalized webhook fields (AC-02)
        $parsed = $gateway->parseWebhook($payload);
        $providerEventId = $parsed['provider_event_id'] ?? '';
        $reference = $parsed['external_reference'] ?? '';
        $amount = (int) ($parsed['amount'] ?? 0);
        $currency = strtoupper((string) ($parsed['currency'] ?? 'VND'));

        if ($providerEventId === '' || $reference === '') {
            return response()->json([
                'error' => 'Malformed IPN payload: missing event ID or reference code.',
            ], 422);
        }

        // 3. Idempotency & Replay Protection (AC-03)
        try {
            ProcessedWebhookEvent::create([
                'provider' => Payment::PROVIDER_SEPAY_SANDBOX,
                'provider_event_id' => $providerEventId,
                'external_reference' => $reference,
                'payload' => $payload,
                'processed_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Already processed - return 200 safely without executing duplicate activation
            return response()->json([
                'message' => 'Webhook event has already been processed.',
            ], 200);
        }

        // 4. Transactional Payment & Membership Activation (AC-01, AC-02, AC-05)
        $result = DB::transaction(function () use ($reference, $amount, $currency, $parsed) {
            $payment = Payment::where('external_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return [
                    'status' => 404,
                    'body' => ['error' => 'Payment reference not found.'],
                ];
            }

            // Strict amount and currency validation (AC-02)
            if ($payment->amount !== $amount || strtoupper($payment->currency) !== $currency || $currency !== 'VND') {
                $payment->update([
                    'status' => Payment::STATUS_FAILED,
                    'payload' => array_merge($payment->payload ?? [], [
                        'mismatch' => 'Amount or currency mismatch',
                        'received_amount' => $amount,
                        'received_currency' => $currency,
                    ]),
                ]);

                return [
                    'status' => 422,
                    'body' => ['error' => 'Payment amount or currency mismatch.'],
                ];
            }

            // If already succeeded, return 200 idempotent
            if ($payment->isSucceeded()) {
                return [
                    'status' => 200,
                    'body' => ['message' => 'Payment was already completed.'],
                ];
            }

            // Transition payment to SUCCEEDED (AC-01)
            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'gateway_transaction_id' => $parsed['gateway_transaction_id'] ?? null,
                'paid_at' => now(),
                'payload' => $parsed,
            ]);

            // Activate matching membership transactionally (AC-01, AC-05)
            $membership = CommunityMembership::where('community_id', $payment->community_id)
                ->where('user_id', $payment->user_id)
                ->lockForUpdate()
                ->first();

            if ($membership) {
                $membership->forceFill(['status' => 'ACTIVE'])->save();
            } else {
                $membership = new CommunityMembership;
                $membership->forceFill([
                    'community_id' => $payment->community_id,
                    'user_id' => $payment->user_id,
                    'status' => 'ACTIVE',
                ])->save();
            }

            // Complete invitation if linked
            if ($payment->community_invitation_id) {
                /** @var CommunityInvitation|null $invite */
                $invite = CommunityInvitation::where('id', $payment->community_invitation_id)
                    ->lockForUpdate()
                    ->first();

                if ($invite && $invite->isPending()) {
                    $invite->forceFill([
                        'status' => 'ACCEPTED',
                        'accepted_at' => now(),
                        'accepted_by_user_id' => $payment->user_id,
                    ])->save();
                }
            }

            return [
                'status' => 200,
                'body' => ['message' => 'Payment verified and membership activated.'],
            ];
        }, 3);

        return response()->json($result['body'], $result['status']);
    }
}
