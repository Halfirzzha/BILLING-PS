<?php

namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PurchaseWalletTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BillingService
    {
        return app(BillingService::class);
    }

    public function test_wallet_purchase_debits_money_and_credits_time(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 60, 'price' => 20000]);

        $this->service()->topUpWallet($user, 50000);
        $entry = $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);

        $user->refresh();
        $this->assertSame(30000, $user->wallet_balance);   // 50000 - 20000
        $this->assertSame(60, $user->remaining_minutes);
        $this->assertSame(60, $entry->minutes);
        $this->assertSame($package->outlet_id, $entry->outlet_id);
    }

    public function test_wallet_purchase_fails_with_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 60, 'price' => 20000]);

        $this->service()->topUpWallet($user, 5000);

        $this->expectException(RuntimeException::class);
        $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);
    }

    public function test_wallet_purchase_fails_for_inactive_package(): void
    {
        $user = User::factory()->create();
        $package = TimePackage::factory()->create(['is_active' => false, 'price' => 1000]);

        $this->service()->topUpWallet($user, 50000);

        $this->expectException(RuntimeException::class);
        $this->service()->purchaseTimePackage($user, $package, PaymentMethod::Wallet);
    }
}
