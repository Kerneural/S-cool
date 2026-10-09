<?php

namespace App\Http\Controllers\Payment;

use App\Actions\Payments\InitiatePaidCheckout;
use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Initiate checkout for a paid community invitation.
     */
    public function initiate(Request $request, string $invitation, InitiatePaidCheckout $action): RedirectResponse
    {
        $token = $request->input('token');
        abort_unless(is_string($token), 404);

        $payment = $action->handle((int) $invitation, $request->user(), $token);

        return redirect()->route('payments.checkout', $payment->external_reference);
    }

    /**
     * Display checkout screen with SePay Sandbox transfer details and QR code.
     */
    public function checkout(Request $request, string $reference, PaymentGateway $gateway): View|RedirectResponse
    {
        $payment = Payment::with(['community', 'user'])
            ->where('external_reference', $reference)
            ->firstOrFail();

        abort_unless($payment->user_id === $request->user()->id, 404);

        if ($payment->isSucceeded()) {
            return redirect()
                ->route('communities.show', $payment->community)
                ->with('status', 'Payment completed. Welcome to the community!');
        }

        $checkoutData = $gateway->generateCheckoutData($payment);

        return view('payments.checkout', [
            'payment' => $payment,
            'community' => $payment->community,
            'checkoutData' => $checkoutData,
        ]);
    }

    /**
     * Check current payment status from database (AC-04: read-only, never mutates access).
     */
    public function status(Request $request, string $reference): View|JsonResponse|RedirectResponse
    {
        $payment = Payment::with(['community'])
            ->where('external_reference', $reference)
            ->firstOrFail();

        abort_unless($payment->user_id === $request->user()->id, 404);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $payment->status,
                'is_succeeded' => $payment->isSucceeded(),
                'redirect_url' => $payment->isSucceeded() ? route('communities.show', $payment->community) : null,
            ]);
        }

        if ($payment->isSucceeded()) {
            return redirect()
                ->route('communities.show', $payment->community)
                ->with('status', 'Payment confirmed. Membership activated!');
        }

        return view('payments.status', [
            'payment' => $payment,
            'community' => $payment->community,
        ]);
    }
}
