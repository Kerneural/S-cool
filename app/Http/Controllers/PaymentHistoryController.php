<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentHistoryController extends Controller
{
    /**
     * Display the authenticated user's personal payment history.
     */
    public function index(Request $request): View
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->with(['community', 'invitation', 'course'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('account.payment-history.index', [
            'payments' => $payments,
        ]);
    }

    /**
     * Display a specific payment attempt / receipt detail for the authenticated user.
     */
    public function show(Request $request, Payment $payment): View
    {
        // Payer sees only its own attempts; a guessed ID returns 404
        abort_unless((int) $payment->user_id === (int) $request->user()->id, 404);

        $community = $payment->community;
        $isActiveMember = $community && $community->isActive() && $community->memberships()
            ->where('user_id', $request->user()->id)
            ->effective()
            ->exists();
        $isCreator = $community && $community->isActive() && $community->isCreator($request->user());

        return view('account.payment-history.show', [
            'payment' => $payment,
            'community' => $community,
            'isActiveMember' => $isActiveMember,
            'isCreator' => $isCreator,
        ]);
    }
}
