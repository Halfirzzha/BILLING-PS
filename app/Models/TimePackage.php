<?php

namespace App\Models;

use Database\Factories\TimePackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'minutes', 'price', 'is_active', 'description'])]
class TimePackage extends Model
{
    /** @use HasFactory<TimePackageFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $package): void {
            $package->slug = filled($package->slug) ? $package->slug : Str::slug($package->name);
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
