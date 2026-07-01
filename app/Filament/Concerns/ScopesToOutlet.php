<?php

namespace App\Filament\Concerns;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Builder;

/**
 * Restricts a resource's query to the current operator's outlet.
 * Developer and Super Admin see every outlet.
 */
trait ScopesToOutlet
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user
            && $user->hasRole(RoleName::Operator->value)
            && ! $user->hasAnyRole([RoleName::Developer->value, RoleName::SuperAdmin->value])
            && $user->outlet_id
        ) {
            $query->where($query->getModel()->getTable().'.outlet_id', $user->outlet_id);
        }

        return $query;
    }
}
