<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Outlet> */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'code' => Str::upper(Str::random(6)),
            'slug' => null,
            'timezone' => 'Asia/Jakarta',
            'address' => fake()->address(),
            'phone' => fake()->numerify('02########'),
            'is_active' => true,
            'settings' => null,
        ];
    }
}
