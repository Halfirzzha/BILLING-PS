<?php

namespace Tests\Feature\Payment;

use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineTopUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_topup_creates_pending_payment(): void
    {
        $user = User::factory()->create();

        $result = app(PaymentService::class)->createTopUp($user, 50000);

        $this->assertSame('pending', $result['payment']->status);
        $this->assertNotNull($result['payment']->provider_ref);
        $this->assertStringContainsString('/confirm', $result['redirect_url']);
    }

    public function test_mark_paid_credits_wallet_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $payment = app(PaymentService::class)->createTopUp($user, 50000)['payment'];

        app(PaymentService::class)->markPaid($payment);
        $this->assertSame(50000, $user->fresh()->wallet_balance);
        $this->assertSame('paid', $payment->fresh()->status);

        // Confirming again must not double-credit.
        app(PaymentService::class)->markPaid($payment->fresh());
        $this->assertSame(50000, $user->fresh()->wallet_balance);
    }

    public function test_webhook_marks_payment_paid_and_credits_wallet(): void
    {
        $user = User::factory()->create();
        $payment = app(PaymentService::class)->createTopUp($user, 25000)['payment'];

        $this->postJson('/api/payments/webhook', [
            'status' => 'paid',
            'provider_ref' => $payment->provider_ref,
        ])->assertOk()->assertJsonPath('ok', true);

        $this->assertSame(25000, $user->fresh()->wallet_balance);
    }

    public function test_portal_topup_confirm_flow_credits_wallet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/portal/topup', ['amount' => 30000])->assertRedirect();

        $payment = Payment::where('user_id', $user->id)->firstOrFail();
        $this->actingAs($user)->get(route('portal.topup.confirm', $payment))
            ->assertRedirect(route('portal'));

        $this->assertSame(30000, $user->fresh()->wallet_balance);
    }
}
