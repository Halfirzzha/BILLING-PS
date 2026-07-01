<?php

namespace App\Models\Concerns;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOutlet
{
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopeForOutlet(Builder $query, int $outletId): Builder
    {
        return $query->where($this->getTable().'.outlet_id', $outletId);
    }
}
