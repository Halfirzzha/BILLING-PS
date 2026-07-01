<?php

namespace Tests\Feature;

use App\Models\Station;
use App\Services\StationDeviceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_station_device_can_pull_queued_command(): void
    {
        $station = Station::create([
            'name' => 'Station Test',
            'code' => 'ST-T',
            'status' => 'idle',
            'device_status' => 'online',
            'app_mode' => 'qr',
            'qr_token' => 'token-st-test',
            'device_token' => 'device-token-test',
            'default_hourly_rate' => 20000,
            'is_active' => true,
        ]);

        app(StationDeviceService::class)->syncStationPresentation($station);

        $response = $this
            ->withHeader('X-Station-Token', 'device-token-test')
            ->getJson("/api/device/stations/{$station->code}/commands/next");

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('command.type', 'show_qr');
    }
}
