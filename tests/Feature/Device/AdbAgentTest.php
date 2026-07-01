<?php

namespace Tests\Feature\Device;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class AdbAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_executes_pending_adb_command(): void
    {
        Process::fake();
        $station = Station::factory()->create(['adb_identifier' => '192.168.1.50:5555']);
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => StationCommandType::Wake->value,
            'status' => StationCommandStatus::Pending->value,
        ]);

        $this->artisan('stations:process-commands')->assertExitCode(0);

        $this->assertSame(StationCommandStatus::Acknowledged, $command->fresh()->status);
        Process::assertRan(fn ($process) => str_contains(implode(' ', $process->command), 'KEYCODE_WAKEUP'));
    }

    public function test_agent_skips_station_without_adb_identifier(): void
    {
        Process::fake();
        $station = Station::factory()->create(['adb_identifier' => null]);
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => StationCommandType::Wake->value,
            'status' => StationCommandStatus::Pending->value,
        ]);

        $this->artisan('stations:process-commands')->assertExitCode(0);

        $this->assertSame(StationCommandStatus::Pending, $command->fresh()->status);
        Process::assertNothingRan();
    }
}
