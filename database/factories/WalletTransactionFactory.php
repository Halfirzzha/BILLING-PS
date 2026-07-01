<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<WalletTransaction> */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'outlet_id' => null,
            'operator_id' => null,
            'type' => WalletTransactionType::TopUp->value,
            'payment_method' => PaymentMethod::Cash->value,
            'amount' => 50000,
            'affects_balance' => true,
            'reference' => 'TXN-'.Str::upper(Str::random(10)),
            'gateway_ref' => null,
            'notes' => null,
            'meta' => null,
        ];
    }
}
