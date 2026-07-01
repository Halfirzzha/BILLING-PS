<?php

namespace Tests\Feature\Foundation;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_outlet_has_defaults_and_users_relation(): void
    {
        $outlet = Outlet::factory()->create(['code' => 'JKT-01']);
        $user = User::factory()->create(['outlet_id' => $outlet->id]);

        $this->assertSame('Asia/Jakarta', $outlet->timezone);
        $this->assertTrue($outlet->is_active);
        $this->assertTrue($outlet->users->contains($user));
        $this->assertSame($outlet->id, $user->outlet->id);
    }

    public function test_settings_are_cast_to_array(): void
    {
        $outlet = Outlet::factory()->create(['settings' => ['tax_percent' => 10]]);

        $this->assertSame(10, $outlet->fresh()->settings['tax_percent']);
    }
}
