<?php

namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Enums\RoleName;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TopUpTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BillingService
    {
        return app(BillingService::class);
    }

    public function test_top_up_increases_wallet_balance_and_assigns_member_role(): void
    {
        $user = User::factory()->create();

        $txn = $this->service()->topUpWallet($user, 50000);

        $this->assertSame(50000, $user->fresh()->wallet_balance);
        $this->assertTrue($user->fresh()->hasRole(RoleName::Member->value));
        $this->assertTrue($txn->affects_balance);
        $this->assertStringStartsWith('TOP-', $txn->reference);
    }

    public function test_top_up_rejects_non_positive_amount(): void
    {
        $user = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->service()->topUpWallet($user, 0, PaymentMethod::Cash);
    }
}
