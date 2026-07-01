<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => 100000,
            'provider' => 'manual',
            'provider_ref' => null,
            'status' => 'pending',
            'paid_at' => null,
            'wallet_transaction_id' => null,
            'payload' => null,
        ];
    }
}
