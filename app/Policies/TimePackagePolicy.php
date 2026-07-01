<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TimePackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimePackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TimePackage');
    }

    public function view(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('View:TimePackage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TimePackage');
    }

    public function update(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('Update:TimePackage');
    }

    public function delete(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('Delete:TimePackage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TimePackage');
    }

    public function restore(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('Restore:TimePackage');
    }

    public function forceDelete(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('ForceDelete:TimePackage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TimePackage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TimePackage');
    }

    public function replicate(AuthUser $authUser, TimePackage $timePackage): bool
    {
        return $authUser->can('Replicate:TimePackage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TimePackage');
    }

}