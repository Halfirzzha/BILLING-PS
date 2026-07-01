<?php

namespace Tests\Feature\Foundation;

use App\Enums\TimeLedgerType;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_minutes_sum_credits_minus_debits(): void
    {
        $user = User::factory()->create();

        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::Credit->value,
            'minutes' => 120,
        ]);
        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::SessionDebit->value,
            'minutes' => -30,
        ]);

        $this->assertSame(90, $user->fresh()->remaining_minutes);
    }

    public function test_remaining_minutes_never_negative(): void
    {
        $user = User::factory()->create();

        TimeLedgerEntry::factory()->for($user)->create([
            'type' => TimeLedgerType::Adjustment->value,
            'minutes' => -50,
        ]);

        $this->assertSame(0, $user->fresh()->remaining_minutes);
    }
}
