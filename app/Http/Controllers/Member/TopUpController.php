<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class TopUpController extends Controller
{
    public function store(Request $request, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1000'],
        ]);

        try {
            $result = $payments->createTopUp($request->user(), (int) $data['amount']);

            return redirect($result['redirect_url']);
        } catch (Throwable $e) {
            return back()->withErrors(['topup' => $e->getMessage()]);
        }
    }

    /**
     * Dev-only confirmation that simulates the gateway callback (fake gateway).
     * Real providers confirm via the webhook endpoint instead.
     */
    public function confirm(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payments->markPaid($payment);

        return redirect()->route('portal')->with('status', 'Top up online berhasil.');
    }
}
