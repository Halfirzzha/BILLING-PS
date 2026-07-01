<?php

namespace Tests\Feature\Foundation;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationCommandModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_defaults_and_casts(): void
    {
        $station = Station::factory()->create();
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => StationCommandType::Wake->value,
            'payload' => ['reason' => 'idle-wake'],
        ]);

        $this->assertSame(StationCommandStatus::Pending, $command->status);
        $this->assertSame(StationCommandType::Wake, $command->type);
        $this->assertSame('idle-wake', $command->payload['reason']);
        $this->assertSame($station->id, $command->station->id);
    }
}
