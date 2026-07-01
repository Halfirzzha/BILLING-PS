<?php

namespace Tests\Feature\Panel;

use App\Enums\RoleName;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(RoleName $role, ?Outlet $outlet = null): User
    {
        $user = User::factory()->create(['outlet_id' => $outlet?->id]);
        $user->assignRole($role->value);

        return $user;
    }

    public function test_developer_can_access_core_resources(): void
    {
        $dev = $this->userWithRole(RoleName::Developer);

        $this->actingAs($dev)->get('/admin')->assertOk();          // dashboard + OperationsOverview widget
        $this->actingAs($dev)->get('/admin/stations')->assertOk();
        $this->actingAs($dev)->get('/admin/outlets')->assertOk();
        $this->actingAs($dev)->get('/admin/users')->assertOk();
    }

    public function test_operator_cannot_access_outlets_resource(): void
    {
        $operator = $this->userWithRole(RoleName::Operator, Outlet::factory()->create());

        $this->actingAs($operator)->get('/admin/outlets')->assertForbidden();
    }

    public function test_operator_sees_only_their_outlet_stations(): void
    {
        $outletA = Outlet::factory()->create();
        $outletB = Outlet::factory()->create();
        Station::factory()->for($outletA)->create(['code' => 'AAA-1']);
        Station::factory()->for($outletB)->create(['code' => 'BBB-1']);
        $operator = $this->userWithRole(RoleName::Operator, $outletA);

        $this->actingAs($operator)->get('/admin/stations')
            ->assertOk()
            ->assertSee('AAA-1')
            ->assertDontSee('BBB-1');
    }

    public function test_member_cannot_access_admin_panel(): void
    {
        $member = $this->userWithRole(RoleName::Member);

        $this->actingAs($member)->get('/admin')->assertForbidden();
    }
}
