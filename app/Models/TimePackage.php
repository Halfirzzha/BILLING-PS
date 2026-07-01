<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOutlet;
use Database\Factories\TimePackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['outlet_id', 'name', 'minutes', 'price', 'is_active', 'sort'])]
class TimePackage extends Model
{
    /** @use HasFactory<TimePackageFactory> */
    use HasFactory;
    use BelongsToOutlet;

    protected function casts(): array
    {
        return [
            'minutes' => 'integer',
            'price' => 'integer',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
