<?php

namespace Tests\Feature\Foundation;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_four_roles_are_seeded(): void
    {
        foreach (RoleName::cases() as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    public function test_developer_passes_any_gate(): void
    {
        $dev = User::factory()->create();
        $dev->assignRole(RoleName::Developer->value);

        $this->assertTrue(Gate::forUser($dev)->allows('anything-at-all'));
    }

    public function test_member_does_not_pass_arbitrary_gate(): void
    {
        $member = User::factory()->create();
        $member->assignRole(RoleName::Member->value);

        $this->assertFalse(Gate::forUser($member)->allows('some-admin-only-ability'));
    }
}
