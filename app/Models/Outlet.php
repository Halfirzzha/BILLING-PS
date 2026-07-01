<?php

namespace App\Models;

use Database\Factories\OutletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'code', 'slug', 'timezone', 'address', 'phone', 'is_active', 'settings'])]
class Outlet extends Model
{
    /** @use HasFactory<OutletFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $outlet): void {
            if (! $outlet->slug) {
                $outlet->slug = Str::slug($outlet->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    public function timePackages(): HasMany
    {
        return $this->hasMany(TimePackage::class);
    }
}
