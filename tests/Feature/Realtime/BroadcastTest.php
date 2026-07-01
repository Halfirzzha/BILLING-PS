<?php

namespace Tests\Feature\Realtime;

use App\Events\SessionEnded;
use App\Events\SessionStarted;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use App\Services\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_session_dispatches_broadcast_event(): void
    {
        Event::fake([SessionStarted::class]);
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();

        app(SessionService::class)->startSession($user, $station);

        Event::assertDispatched(SessionStarted::class);
    }

    public function test_ending_session_dispatches_broadcast_event(): void
    {
        Event::fake([SessionEnded::class]);
        $user = User::factory()->create();
        TimeLedgerEntry::factory()->for($user)->create(['minutes' => 60]);
        $station = Station::factory()->create();
        $session = app(SessionService::class)->startSession($user, $station);

        app(SessionService::class)->endSession($session->fresh());

        Event::assertDispatched(SessionEnded::class);
    }

    public function test_event_targets_private_outlet_channel(): void
    {
        $session = PlaySession::factory()->create();

        $channels = (new SessionStarted($session))->broadcastOn();

        $this->assertSame("private-outlet.{$session->outlet_id}.stations", $channels[0]->name);
    }
}
