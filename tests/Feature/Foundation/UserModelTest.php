<?php

namespace Tests\Feature\Foundation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_code_is_generated_on_create(): void
    {
        $user = User::factory()->create(['member_code' => null]);

        $this->assertMatchesRegularExpression('/^MBR-\d{6}$/', $user->member_code);
    }

    // Note: balance-derivation defaults are covered in WalletBalanceTest (Task 6),
    // TimeBalanceTest (Task 7) and SeedIntegrationTest (Task 12), once the ledger
    // tables exist. Asserting them here would hit missing tables under RefreshDatabase.

    public function test_is_active_casts_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);

        $this->assertTrue($user->is_active);
    }
}
