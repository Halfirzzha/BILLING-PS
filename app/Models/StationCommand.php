<?php

namespace App\Models;

use App\Enums\StationCommandStatus;
use App\Enums\StationCommandType;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\StationCommandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'station_id', 'type', 'status', 'payload', 'dispatched_at', 'acknowledged_at', 'error', 'created_by'])]
class StationCommand extends Model
{
    /** @use HasFactory<StationCommandFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'type' => StationCommandType::class,
            'status' => StationCommandStatus::class,
            'payload' => 'array',
            'dispatched_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
