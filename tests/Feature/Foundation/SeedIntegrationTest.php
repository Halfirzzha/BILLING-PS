<?php

namespace Tests\Feature\Foundation;

use App\Enums\RoleName;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\TimePackage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_world_is_created(): void
    {
        $this->assertSame(2, Outlet::count());
        $this->assertGreaterThanOrEqual(4, User::count());
        $this->assertGreaterThan(0, TimePackage::count());
        $this->assertGreaterThan(0, Station::count());
    }

    public function test_demo_accounts_have_expected_roles(): void
    {
        $this->assertTrue(User::where('email', 'developer@billingps5.local')->first()->hasRole(RoleName::Developer->value));
        $this->assertTrue(User::where('email', 'superadmin@billingps5.local')->first()->hasRole(RoleName::SuperAdmin->value));

        $operator = User::where('email', 'operator@billingps5.local')->first();
        $this->assertTrue($operator->hasRole(RoleName::Operator->value));
        $this->assertNotNull($operator->outlet_id);

        $this->assertTrue(User::where('email', 'member@billingps5.local')->first()->hasRole(RoleName::Member->value));
    }

    public function test_seeded_member_starts_with_zero_balances(): void
    {
        $member = User::where('email', 'member@billingps5.local')->first();

        $this->assertSame(0, $member->wallet_balance);
        $this->assertSame(0, $member->remaining_minutes);
    }
}
