<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WalletTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WalletTransaction');
    }

    public function view(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('View:WalletTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WalletTransaction');
    }

    public function update(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('Update:WalletTransaction');
    }

    public function delete(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('Delete:WalletTransaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WalletTransaction');
    }

    public function restore(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('Restore:WalletTransaction');
    }

    public function forceDelete(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('ForceDelete:WalletTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WalletTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WalletTransaction');
    }

    public function replicate(AuthUser $authUser, WalletTransaction $walletTransaction): bool
    {
        return $authUser->can('Replicate:WalletTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WalletTransaction');
    }

}