<?php

namespace App\Models;

use App\Enums\PlaySessionStatus;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\PlaySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'station_id', 'user_id', 'status', 'payment_method', 'started_at', 'planned_end_at', 'ended_at', 'started_with_minutes', 'consumed_minutes', 'minutes_debited', 'ended_by', 'notes'])]
class PlaySession extends Model
{
    /** @use HasFactory<PlaySessionFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'status' => PlaySessionStatus::class,
            'started_at' => 'datetime',
            'planned_end_at' => 'datetime',
            'ended_at' => 'datetime',
            'started_with_minutes' => 'integer',
            'consumed_minutes' => 'integer',
            'minutes_debited' => 'integer',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
