<?php

namespace Tests\Feature\Session;

use App\Enums\PlaySessionStatus;
use App\Enums\StationStatus;
use App\Jobs\EndExpiredSession;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoStopTest extends TestCase
{
    use RefreshDatabase;

    private function expiredSession(int $minutes = 60): PlaySession
    {
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => $minutes]);
        $station = Station::factory()->create(['status' => StationStatus::Active->value]);

        $session = PlaySession::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'user_id' => $user->id,
            'status' => PlaySessionStatus::Active->value,
            'started_at' => now()->subMinutes($minutes + 1),
            'planned_end_at' => now()->subMinute(),
            'started_with_minutes' => $minutes,
        ]);
        $station->update(['current_session_id' => $session->id]);

        return $session;
    }

    public function test_stop_expired_sessions_ends_and_debits_full_time(): void
    {
        $session = $this->expiredSession(60);

        $count = app(SessionService::class)->stopExpiredSessions();

        $this->assertSame(1, $count);
        $session->refresh();
        $this->assertSame(PlaySessionStatus::Completed, $session->status);
        $this->assertSame(60, $session->minutes_debited);
        $this->assertSame(0, $session->user->fresh()->remaining_minutes);
        $this->assertSame(StationStatus::Idle, $session->station->fresh()->status);
    }

    public function test_not_yet_expired_session_stays_active(): void
    {
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();
        $session = PlaySession::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'user_id' => $user->id,
            'status' => PlaySessionStatus::Active->value,
            'started_at' => now(),
            'planned_end_at' => now()->addMinutes(30),
            'started_with_minutes' => 60,
        ]);

        $count = app(SessionService::class)->stopExpiredSessions();

        $this->assertSame(0, $count);
        $this->assertSame(PlaySessionStatus::Active, $session->fresh()->status);
    }

    public function test_auto_stop_command_ends_expired_session(): void
    {
        $session = $this->expiredSession(60);

        $this->artisan('sessions:auto-stop')->assertExitCode(0);

        $this->assertSame(PlaySessionStatus::Completed, $session->fresh()->status);
    }

    public function test_job_ends_expired_session(): void
    {
        $session = $this->expiredSession(60);

        (new EndExpiredSession($session->id))->handle(app(SessionService::class));

        $this->assertSame(PlaySessionStatus::Completed, $session->fresh()->status);
    }
}
