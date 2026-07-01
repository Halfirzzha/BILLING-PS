<?php

namespace Tests\Feature\Foundation;

use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_sums_only_affecting_transactions(): void
    {
        $user = User::factory()->create();

        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 50000,
            'affects_balance' => true,
        ]);
        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::TimePurchase->value,
            'payment_method' => PaymentMethod::Wallet->value,
            'amount' => -20000,
            'affects_balance' => true,
        ]);
        // Cash sale must NOT affect wallet balance.
        WalletTransaction::factory()->for($user)->create([
            'type' => WalletTransactionType::CashSale->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 15000,
            'affects_balance' => false,
        ]);

        $this->assertSame(30000, $user->fresh()->wallet_balance);
    }
}
