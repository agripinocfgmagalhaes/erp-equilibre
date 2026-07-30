<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Prestador;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrestadorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Prestador');
    }

    public function view(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('View:Prestador');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Prestador');
    }

    public function update(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('Update:Prestador');
    }

    public function delete(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('Delete:Prestador');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Prestador');
    }

    public function restore(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('Restore:Prestador');
    }

    public function forceDelete(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('ForceDelete:Prestador');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Prestador');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Prestador');
    }

    public function replicate(AuthUser $authUser, Prestador $prestador): bool
    {
        return $authUser->can('Replicate:Prestador');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Prestador');
    }

}