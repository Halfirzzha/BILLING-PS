<?php

namespace Database\Factories;

use App\Enums\TimeLedgerType;
use App\Models\TimeLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimeLedgerEntry> */
class TimeLedgerEntryFactory extends Factory
{
    protected $model = TimeLedgerEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'outlet_id' => null,
            'operator_id' => null,
            'time_package_id' => null,
            'play_session_id' => null,
            'type' => TimeLedgerType::Credit->value,
            'minutes' => 60,
            'notes' => null,
            'meta' => null,
        ];
    }
}
