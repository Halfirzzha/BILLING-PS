<?php

namespace Tests\Feature\Session;

use App\Enums\PlaySessionStatus;
use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Jobs\EndExpiredSession;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class SessionStartTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SessionService
    {
        return app(SessionService::class);
    }

    private function memberWithMinutes(int $minutes): User
    {
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => $minutes]);

        return $user;
    }

    public function test_start_creates_active_session_and_updates_station(): void
    {
        Queue::fake();
        $user = $this->memberWithMinutes(60);
        $station = Station::factory()->create();

        $session = $this->service()->startSession($user, $station);
        $station->refresh();

        $this->assertSame(PlaySessionStatus::Active, $session->status);
        $this->assertSame(60, $session->started_with_minutes);
        $this->assertNotNull($session->planned_end_at);
        $this->assertSame(StationStatus::Active, $station->status);
        $this->assertSame(StationAppMode::Session, $station->app_mode);
        $this->assertSame($session->id, $station->current_session_id);
        $this->assertDatabaseHas('station_commands', [
            'station_id' => $station->id,
            'type' => 'refresh_state',
            'status' => 'pending',
        ]);
        Queue::assertPushed(EndExpiredSession::class);
    }

    public function test_start_fails_without_time_balance(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $station = Station::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->service()->startSession($user, $station);
    }

    public function test_start_fails_when_station_busy(): void
    {
        Queue::fake();
        $station = Station::factory()->create();
        PlaySession::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'status' => PlaySessionStatus::Active->value,
        ]);
        $user = $this->memberWithMinutes(60);

        $this->expectException(RuntimeException::class);
        $this->service()->startSession($user, $station);
    }

    public function test_start_fails_when_user_already_playing(): void
    {
        Queue::fake();
        $user = $this->memberWithMinutes(60);
        PlaySession::factory()->create([
            'user_id' => $user->id,
            'status' => PlaySessionStatus::Active->value,
        ]);
        $station = Station::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->service()->startSession($user, $station);
    }

    public function test_start_fails_when_station_in_maintenance(): void
    {
        Queue::fake();
        $user = $this->memberWithMinutes(60);
        $station = Station::factory()->create(['status' => StationStatus::Maintenance->value]);

        $this->expectException(RuntimeException::class);
        $this->service()->startSession($user, $station);
    }
}
