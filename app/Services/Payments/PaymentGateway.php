<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Swappable online payment provider. Concrete implementations (Midtrans, Xendit)
 * are added later; the app depends only on this contract.
 */
interface PaymentGateway
{
    public function name(): string;

    /**
     * Create a charge for a pending payment.
     *
     * @return array{provider_ref: string, redirect_url: string}
     */
    public function createCharge(Payment $payment): array;

    /**
     * Verify an incoming webhook and return the provider_ref of a PAID payment,
     * or null if the webhook is not a successful payment / fails verification.
     */
    public function resolvePaidReference(Request $request): ?string;
}
