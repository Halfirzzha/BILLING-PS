<?php

namespace App\Models;

use Database\Factories\StationCommandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'station_id',
    'requested_by',
    'type',
    'status',
    'attempts',
    'payload',
    'failure_message',
    'sent_at',
    'acknowledged_at',
    'processed_at',
])]
class StationCommand extends Model
{
    /** @use HasFactory<StationCommandFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
