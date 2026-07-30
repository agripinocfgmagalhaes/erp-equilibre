<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FasePadrao;
use Illuminate\Auth\Access\HandlesAuthorization;

class FasePadraoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FasePadrao');
    }

    public function view(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('View:FasePadrao');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FasePadrao');
    }

    public function update(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('Update:FasePadrao');
    }

    public function delete(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('Delete:FasePadrao');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FasePadrao');
    }

    public function restore(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('Restore:FasePadrao');
    }

    public function forceDelete(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('ForceDelete:FasePadrao');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FasePadrao');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FasePadrao');
    }

    public function replicate(AuthUser $authUser, FasePadrao $fasePadrao): bool
    {
        return $authUser->can('Replicate:FasePadrao');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FasePadrao');
    }

}