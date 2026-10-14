<?php

namespace App\Http\Controllers\Payment;

use App\Actions\Payments\InitiateCourseCheckout;
use App\Actions\Payments\InitiatePaidCheckout;
use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Initiate checkout for a paid course purchase.
     */
    public function initiateCourse(Request $request, Community $community, Course $course, InitiateCourseCheckout $action): RedirectResponse
    {
        $payment = $action->handle($community, $course, $request->user());

        return redirect()->route('payments.checkout', $payment->external_reference);
    }

    /**
     * Display checkout screen with SePay Sandbox transfer details and QR code.
     */
    public function checkout(Request $request, string $reference, PaymentGateway $gateway): View|RedirectResponse
    {
        $payment = Payment::with(['community', 'user', 'course'])
            ->where('external_reference', $reference)
            ->firstOrFail();

        abort_unless($payment->user_id === $request->user()->id, 404);

        if ($payment->isFailed() || $payment->isCancelled()) {
            return redirect()->route('payments.status', $reference);
        }

        if ($payment->isSucceeded()) {
            if (! $payment->grantsCurrentAccess($request->user())) {
                return redirect()->route('payments.status', $reference);
            }

            if ($payment->isCourseAccess() && $payment->course) {
                return redirect()
                    ->route('communities.courses.show', [$payment->community, $payment->course])
                    ->with('status', 'Course unlocked! Welcome to the course.');
            }

            return redirect()
                ->route('communities.show', $payment->community)
                ->with('status', 'Payment completed. Welcome to the community!');
        }

        abort_if(($payment->isCourseAccess() || $payment->isMembershipRenewal()) && ! $payment->canResumeCheckout($request->user()), 404);

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
        $payment = Payment::with(['community', 'course'])
            ->where('external_reference', $reference)
            ->firstOrFail();

        abort_unless($payment->user_id === $request->user()->id, 404);

        $succeededRedirectUrl = null;
        if ($payment->isSucceeded() && $payment->grantsCurrentAccess($request->user())) {
            $succeededRedirectUrl = ($payment->isCourseAccess() && $payment->course)
                ? route('communities.courses.show', [$payment->community, $payment->course])
                : route('communities.show', $payment->community);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $payment->status,
                'is_succeeded' => $payment->isSucceeded(),
                'redirect_url' => $succeededRedirectUrl,
            ]);
        }

        if ($payment->isSucceeded() && $payment->grantsCurrentAccess($request->user())) {
            if ($payment->isCourseAccess() && $payment->course) {
                return redirect()
                    ->route('communities.courses.show', [$payment->community, $payment->course])
                    ->with('status', 'Payment confirmed. Course unlocked!');
            }

            return redirect()
                ->route('communities.show', $payment->community)
                ->with('status', $payment->isMembershipRenewal() ? 'Payment confirmed. Membership term renewed!' : 'Payment confirmed. Membership activated!');
        }

        return view('payments.status', [
            'payment' => $payment,
            'community' => $payment->community,
            'accessAvailable' => $payment->grantsCurrentAccess($request->user()),
        ]);
    }

    public function cancel(Request $request, string $reference): RedirectResponse
    {
        DB::transaction(function () use ($request, $reference): void {
            User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->where('external_reference', $reference)
                ->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            if ($payment->isPending()) {
                $payment->update(['status' => Payment::STATUS_CANCELLED]);
            }
        }, 3);

        return redirect()->route('payments.status', $reference);
    }
}
