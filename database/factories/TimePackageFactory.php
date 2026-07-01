<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\TimePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimePackage> */
class TimePackageFactory extends Factory
{
    protected $model = TimePackage::class;

    public function definition(): array
    {
        $minutes = fake()->randomElement([30, 60, 120, 180]);

        return [
            'outlet_id' => Outlet::factory(),
            'name' => $minutes.' Menit',
            'minutes' => $minutes,
            'price' => $minutes * 300,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
