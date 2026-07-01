<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly BillingService $billing,
    ) {}

    /**
     * Create a pending online top-up and its gateway charge.
     *
     * @return array{payment: Payment, redirect_url: string}
     */
    public function createTopUp(User $user, int $amount): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Jumlah top up harus lebih dari nol.');
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'provider' => $this->gateway->name(),
            'status' => 'pending',
        ]);

        $charge = $this->gateway->createCharge($payment);
        $payment->update([
            'provider_ref' => $charge['provider_ref'],
            'payload' => $charge,
        ]);

        return ['payment' => $payment, 'redirect_url' => $charge['redirect_url']];
    }

    /**
     * Mark a payment paid and credit the wallet exactly once (idempotent).
     */
    public function markPaid(Payment $payment): Payment
    {
        if ($payment->status === 'paid') {
            return $payment;
        }

        return DB::transaction(function () use ($payment): Payment {
            $fresh = Payment::query()->lockForUpdate()->find($payment->id);

            if (! $fresh || $fresh->status === 'paid') {
                return $fresh ?? $payment;
            }

            $txn = $this->billing->topUpWallet(
                $fresh->user,
                $fresh->amount,
                PaymentMethod::Gateway,
                null,
                null,
                "Top up online ({$fresh->provider})",
            );

            $fresh->update([
                'status' => 'paid',
                'paid_at' => now(),
                'wallet_transaction_id' => $txn->id,
            ]);

            return $fresh;
        });
    }

    public function handleWebhook(Request $request): ?Payment
    {
        $reference = $this->gateway->resolvePaidReference($request);

        if (! $reference) {
            return null;
        }

        $payment = Payment::where('provider_ref', $reference)->first();

        return $payment ? $this->markPaid($payment) : null;
    }
}
