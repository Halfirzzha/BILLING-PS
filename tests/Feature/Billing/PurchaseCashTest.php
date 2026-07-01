<?php

namespace Tests\Feature\Billing;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseCashTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_purchase_credits_time_without_touching_wallet(): void
    {
        $user = User::factory()->create();
        $operator = User::factory()->create();
        $package = TimePackage::factory()->create(['minutes' => 120, 'price' => 34000]);

        $entry = app(BillingService::class)
            ->purchaseTimePackage($user, $package, PaymentMethod::Cash, $operator);

        $user->refresh();
        $this->assertSame(0, $user->wallet_balance);        // cash sale must not change wallet
        $this->assertSame(120, $user->remaining_minutes);
        $this->assertSame($operator->id, $entry->operator_id);
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $user->id,
            'type' => 'cash_sale',
            'affects_balance' => false,
            'amount' => 34000,
        ]);
    }
}
