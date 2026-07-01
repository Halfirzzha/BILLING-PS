<?php

namespace App\Models;

use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['outlet_id', 'code', 'name', 'status', 'app_mode', 'is_active', 'qr_token', 'device_token', 'adb_identifier', 'current_session_id', 'last_heartbeat_at'])]
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected static function booted(): void
    {
        static::creating(function (self $station): void {
            $station->qr_token ??= Str::random(40);
            $station->device_token ??= Str::random(40);
        });
    }

    protected function casts(): array
    {
        return [
            'status' => StationStatus::class,
            'app_mode' => StationAppMode::class,
            'is_active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(StationCommand::class);
    }
}
