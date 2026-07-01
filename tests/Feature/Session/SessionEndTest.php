<?php

namespace Tests\Feature\Session;

use App\Enums\PlaySessionStatus;
use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class SessionEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function service(): SessionService
    {
        return app(SessionService::class);
    }

    public function test_end_debits_elapsed_time_and_resets_station(): void
    {
        $this->travelTo(now()->startOfSecond()); // whole-second freeze: DB truncates sub-seconds
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();

        $session = $this->service()->startSession($user, $station);
        // Simulate 30 minutes elapsed.
        $session->update([
            'started_at' => now()->subMinutes(30),
            'planned_end_at' => now()->addMinutes(30),
        ]);

        $ended = $this->service()->endSession($session->fresh());

        $this->assertSame(PlaySessionStatus::Completed, $ended->status);
        $this->assertSame(30, $ended->consumed_minutes);
        $this->assertSame(30, $ended->minutes_debited);
        $this->assertSame(30, $user->fresh()->remaining_minutes);   // 60 - 30

        $station->refresh();
        $this->assertSame(StationStatus::Idle, $station->status);
        $this->assertSame(StationAppMode::Qr, $station->app_mode);
        $this->assertNull($station->current_session_id);
    }

    public function test_ending_a_completed_session_throws(): void
    {
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();

        $session = $this->service()->startSession($user, $station);
        $this->service()->endSession($session->fresh());

        $this->expectException(RuntimeException::class);
        $this->service()->endSession($session->fresh());
    }
}
