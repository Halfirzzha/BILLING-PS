<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Station> */
class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'code' => 'ST-'.Str::upper(Str::random(5)),
            'name' => 'Station '.fake()->numberBetween(1, 20),
            'status' => 'idle',
            'app_mode' => 'qr',
            'is_active' => true,
            'qr_token' => null,
            'device_token' => null,
            'adb_identifier' => null,
        ];
    }
}
