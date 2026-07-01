<?php

namespace Tests\Feature\Foundation;

use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Outlet;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_station_defaults_and_token_generation(): void
    {
        $outlet = Outlet::factory()->create();
        $station = Station::factory()->for($outlet)->create([
            'qr_token' => null,
            'device_token' => null,
        ]);

        $this->assertSame(StationStatus::Idle, $station->status);
        $this->assertSame(StationAppMode::Qr, $station->app_mode);
        $this->assertTrue($station->is_active);
        $this->assertNotEmpty($station->qr_token);
        $this->assertNotEmpty($station->device_token);
        $this->assertSame($outlet->id, $station->outlet->id);
    }
}
