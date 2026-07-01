<?php

namespace App\Models;

use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'code',
    'status',
    'device_status',
    'app_mode',
    'qr_token',
    'device_token',
    'location',
    'adb_identifier',
    'current_screen',
    'device_version',
    'last_heartbeat_at',
    'last_command_synced_at',
    'tv_label',
    'ps_label',
    'default_hourly_rate',
    'is_active',
    'notes',
])]
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $station): void {
            $station->code ??= Str::upper(Str::slug($station->name)) . '-' . random_int(10, 99);
            $station->qr_token ??= (string) Str::uuid();
            $station->device_token ??= Str::random(40);
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_heartbeat_at' => 'datetime',
            'last_command_synced_at' => 'datetime',
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

    public function activeSession(): Attribute
    {
        return Attribute::get(fn () => $this->playSessions()->where('status', 'active')->latest('started_at')->first());
    }
}
