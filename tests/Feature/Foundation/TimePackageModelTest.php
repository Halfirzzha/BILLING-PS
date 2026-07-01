<?php

namespace Tests\Feature\Foundation;

use App\Models\Outlet;
use App\Models\TimePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimePackageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_belongs_to_outlet_and_casts_ints(): void
    {
        $outlet = Outlet::factory()->create();
        $package = TimePackage::factory()->for($outlet)->create([
            'minutes' => 60,
            'price' => 20000,
        ]);

        $this->assertSame($outlet->id, $package->outlet->id);
        $this->assertSame(60, $package->minutes);
        $this->assertSame(20000, $package->price);
        $this->assertTrue($package->is_active);
    }
}
