<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PlaySession;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlaySessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PlaySession');
    }

    public function view(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('View:PlaySession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PlaySession');
    }

    public function update(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('Update:PlaySession');
    }

    public function delete(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('Delete:PlaySession');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PlaySession');
    }

    public function restore(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('Restore:PlaySession');
    }

    public function forceDelete(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('ForceDelete:PlaySession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PlaySession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PlaySession');
    }

    public function replicate(AuthUser $authUser, PlaySession $playSession): bool
    {
        return $authUser->can('Replicate:PlaySession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PlaySession');
    }

}