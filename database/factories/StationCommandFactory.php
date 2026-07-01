<?php

namespace Database\Factories;

use App\Enums\StationCommandType;
use App\Models\Outlet;
use App\Models\Station;
use App\Models\StationCommand;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StationCommand> */
class StationCommandFactory extends Factory
{
    protected $model = StationCommand::class;

    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'station_id' => Station::factory(),
            'type' => StationCommandType::RefreshState->value,
            'status' => 'pending',
            'payload' => null,
            'created_by' => null,
        ];
    }
}
