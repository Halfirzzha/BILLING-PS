<?php

namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlaySession> */
class PlaySessionFactory extends Factory
{
    protected $model = PlaySession::class;

    public function definition(): array
    {
        $station = Station::factory()->create();

        return [
            'outlet_id' => $station->outlet_id,
            'station_id' => $station->id,
            'user_id' => User::factory(),
            'status' => 'active',
            'payment_method' => 'time_balance',
            'started_at' => now(),
            'planned_end_at' => now()->addMinutes(60),
            'started_with_minutes' => 60,
            'consumed_minutes' => 0,
            'minutes_debited' => 0,
            'notes' => null,
        ];
    }
}
