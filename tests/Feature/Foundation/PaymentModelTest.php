<?php

namespace Tests\Feature\Foundation;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_defaults_to_pending_and_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->for($user)->create(['amount' => 100000]);

        $this->assertSame('pending', $payment->status);
        $this->assertSame(100000, $payment->amount);
        $this->assertSame($user->id, $payment->user->id);
        $this->assertNull($payment->paid_at);
    }
}
