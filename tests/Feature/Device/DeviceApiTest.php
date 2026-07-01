<?php

namespace Tests\Feature\Device;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Station;
use App\Models\StationCommand;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DeviceApiTest extends TestCase
{
    use RefreshDatabase;

    private function auth(Station $station): array
    {
        return ['X-Station-Token' => $station->device_token];
    }

    public function test_heartbeat_requires_valid_token(): void
    {
        $station = Station::factory()->create();
        $url = "/api/device/stations/{$station->code}/heartbeat";

        $this->postJson($url)->assertUnauthorized();
        $this->withHeaders(['X-Station-Token' => 'wrong'])->postJson($url)->assertUnauthorized();

        $this->withHeaders($this->auth($station))->postJson($url)->assertOk();
        $this->assertNotNull($station->fresh()->last_heartbeat_at);
    }

    public function test_state_returns_qr_when_idle(): void
    {
        $station = Station::factory()->create();

        $this->withHeaders($this->auth($station))
            ->getJson("/api/device/stations/{$station->code}/state")
            ->assertOk()
            ->assertJsonPath('mode', 'qr')
            ->assertJsonPath('session', null)
            ->assertJsonPath('qr.join_url', route('join', $station->qr_token));
    }

    public function test_state_returns_session_when_active(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();
        app(SessionService::class)->startSession($user, $station);

        $this->withHeaders($this->auth($station))
            ->getJson("/api/device/stations/{$station->code}/state")
            ->assertOk()
            ->assertJsonPath('mode', 'session')
            ->assertJsonPath('session.remaining_minutes', 60);
    }

    public function test_next_command_returns_and_marks_dispatched(): void
    {
        $station = Station::factory()->create();
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'type' => StationCommandType::RefreshState->value,
            'status' => StationCommandStatus::Pending->value,
        ]);

        $this->withHeaders($this->auth($station))
            ->getJson("/api/device/stations/{$station->code}/commands/next")
            ->assertOk()
            ->assertJsonPath('command.id', $command->id)
            ->assertJsonPath('command.type', 'refresh_state');

        $this->assertSame(StationCommandStatus::Dispatched, $command->fresh()->status);

        // No more pending commands.
        $this->withHeaders($this->auth($station))
            ->getJson("/api/device/stations/{$station->code}/commands/next")
            ->assertOk()
            ->assertJsonPath('command', null);
    }

    public function test_acknowledge_marks_command(): void
    {
        $station = Station::factory()->create();
        $command = StationCommand::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'status' => StationCommandStatus::Dispatched->value,
        ]);

        $this->withHeaders($this->auth($station))
            ->postJson("/api/device/stations/{$station->code}/commands/{$command->id}/acknowledge", ['success' => true])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(StationCommandStatus::Acknowledged, $command->fresh()->status);
    }
}
