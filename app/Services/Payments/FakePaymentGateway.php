<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Development gateway. "Redirects" to an in-app confirm page that simulates a
 * successful payment. Swap for Midtrans/Xendit in production via PAYMENT_GATEWAY.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createCharge(Payment $payment): array
    {
        return [
            'provider_ref' => 'FAKE-'.Str::upper(Str::random(12)),
            'redirect_url' => route('portal.topup.confirm', $payment),
        ];
    }

    public function resolvePaidReference(Request $request): ?string
    {
        return $request->input('status') === 'paid'
            ? $request->string('provider_ref')->toString()
            : null;
    }
}
