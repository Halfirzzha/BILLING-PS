<?php

namespace App\Models;

use Database\Factories\PlaySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'station_id',
    'time_package_id',
    'status',
    'payment_method',
    'started_with_minutes',
    'consumed_minutes',
    'overage_minutes',
    'started_at',
    'ended_at',
    'notes',
    'meta',
])]
class PlaySession extends Model
{
    /** @use HasFactory<PlaySessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function timePackage(): BelongsTo
    {
        return $this->belongsTo(TimePackage::class);
    }

    public function elapsedMinutes(): int
    {
        $endAt = $this->ended_at ?? Carbon::now();

        return max(1, (int) ceil($this->started_at->diffInSeconds($endAt) / 60));
    }

    public function remainingSessionMinutes(): int
    {
        return max(0, $this->started_with_minutes - $this->elapsedMinutes());
    }
}
