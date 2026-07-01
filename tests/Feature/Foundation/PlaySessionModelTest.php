<?php

namespace Tests\Feature\Foundation;

use App\Enums\PlaySessionStatus;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaySessionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_relations_and_casts(): void
    {
        $station = Station::factory()->create();
        $user = User::factory()->create();

        $session = PlaySession::factory()->create([
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'user_id' => $user->id,
            'started_with_minutes' => 60,
        ]);

        $this->assertSame(PlaySessionStatus::Active, $session->status);
        $this->assertSame($station->id, $session->station->id);
        $this->assertSame($user->id, $session->user->id);
        $this->assertSame(60, $session->started_with_minutes);
        $this->assertTrue($user->playSessions->contains($session));
    }
}
