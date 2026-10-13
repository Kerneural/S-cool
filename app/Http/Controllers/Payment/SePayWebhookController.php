<?php

namespace App\Http\Controllers\Payment;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Jobs\SendMemberActivatedNotification;
use App\Models\Community;
use App\Models\CommunityInvitation;
use App\Models\CommunityMembership;
use App\Models\Payment;
use App\Models\ProcessedWebhookEvent;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        try {
            $parsed = $gateway->parseWebhook($payload);
        } catch (ValidationException) {
            return response()->json(['success' => false, 'error' => 'Invalid payment notification.'], 422);
        }
        $providerEventId = $parsed['provider_event_id'] ?? '';
        $reference = $parsed['external_reference'] ?? '';
        $amount = (int) ($parsed['amount'] ?? 0);
        $currency = strtoupper((string) ($parsed['currency'] ?? 'VND'));

        if ($providerEventId === '' || $reference === '') {
            return response()->json([
                'error' => 'Malformed IPN payload: missing event ID or reference code.',
            ], 422);
        }

        // 3. Transactional Idempotency + Payment & Membership Activation (AC-01, AC-02, AC-03, AC-05)
        $snapshot = Payment::query()->where('external_reference', $reference)->first();
        if (! $snapshot) {
            return response()->json(['success' => false, 'error' => 'Payment reference not found.'], 404);
        }

        $result = DB::transaction(function () use ($providerEventId, $reference, $amount, $currency, $parsed, $snapshot) {
            // Same lock order as checkout: actor -> tenant -> invite -> membership -> payment.
            $actor = User::query()->whereKey($snapshot->user_id)->lockForUpdate()->first();
            $community = Community::query()->whereKey($snapshot->community_id)->lockForUpdate()->first();
            $invite = CommunityInvitation::query()->whereKey($snapshot->community_invitation_id)->lockForUpdate()->first();
            $membership = CommunityMembership::where('community_id', $snapshot->community_id)
                ->where('user_id', $snapshot->user_id)->lockForUpdate()->first();
            $payment = Payment::where('external_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return [
                    'status' => 404,
                    'body' => ['error' => 'Payment reference not found.'],
                ];
            }

            // Idempotency & Replay Protection (AC-03)
            try {
                ProcessedWebhookEvent::create([
                    'provider' => Payment::PROVIDER_SEPAY_SANDBOX,
                    'provider_event_id' => $providerEventId,
                    'external_reference' => $reference,
                    'payload' => $parsed,
                    'processed_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                $event = ProcessedWebhookEvent::query()->where('provider', Payment::PROVIDER_SEPAY_SANDBOX)
                    ->where('provider_event_id', $providerEventId)->first();
                if (! $event || $event->external_reference !== $reference) {
                    return ['status' => 422, 'body' => ['error' => 'Event is already bound to another payment.']];
                }

                // Already processed - return 200 safely without executing duplicate activation
                return [
                    'status' => 200,
                    'body' => ['message' => 'Webhook event has already been processed.'],
                ];
            }

            // If already succeeded, return 200 idempotent
            if ($payment->isSucceeded()) {
                return [
                    'status' => 200,
                    'body' => ['message' => 'Payment was already completed.'],
                ];
            }

            if (! $payment->isPending()) {
                return ['status' => 409, 'body' => ['error' => 'Payment is already terminal. Start a new attempt.']];
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

            $resultStatus = $parsed['status'] ?? null;
            if (! in_array($resultStatus, [Payment::STATUS_SUCCEEDED, Payment::STATUS_FAILED, Payment::STATUS_CANCELLED], true)) {
                return ['status' => 422, 'body' => ['error' => 'Unsupported payment result.']];
            }

            if ($resultStatus !== Payment::STATUS_SUCCEEDED) {
                $payment->update(['status' => $resultStatus]);

                return ['status' => 200, 'body' => ['message' => 'Payment result recorded without granting access.']];
            }

            // A late IPN must not undo moderation or a member's departure.
            if (! $community || ! $community->isActive() || $community->access_mode !== 'PAID'
                || ! $actor || ! $actor->hasVerifiedEmail() || $community->isCreator($actor)
                || ! $invite || ! $invite->isPending() || $invite->community_id !== $community->id
                || $invite->normalized_email !== strtolower(trim($actor->email))) {
                return ['status' => 409, 'body' => ['error' => 'Invitation is no longer eligible for activation.']];
            }

            if (! $membership || $membership->status !== 'PENDING_PAYMENT') {
                return ['status' => 409, 'body' => ['error' => 'Membership is not awaiting this payment.']];
            }

            // Transition payment to SUCCEEDED (AC-01)
            $payment->update([
                'status' => Payment::STATUS_SUCCEEDED,
                'gateway_transaction_id' => $parsed['gateway_transaction_id'] ?? null,
                'paid_at' => now(),
                'payload' => $parsed,
            ]);

            // Activate matching membership transactionally (AC-01, AC-05)
            $membership->forceFill(['status' => 'ACTIVE'])->save();

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

            if ($payment->community_invitation_id && isset($invite) && $invite) {
                SendMemberActivatedNotification::dispatch($payment->community_id, $membership->id, 'invitation', $invite->id);
            } else {
                SendMemberActivatedNotification::dispatch($payment->community_id, $membership->id, 'payment', $payment->id);
            }

            return [
                'status' => 200,
                'body' => ['message' => 'Payment verified and membership activated.'],
            ];
        }, 3);

        return response()->json(['success' => $result['status'] === 200] + $result['body'], $result['status']);
    }
}
