<?php

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Live station monitoring per outlet. Admins see any outlet; operators only theirs.
Broadcast::channel('outlet.{outletId}.stations', function (User $user, int $outletId): bool {
    return $user->isAdmin()
        || ($user->hasRole(RoleName::Operator->value) && (int) $user->outlet_id === $outletId);
});
