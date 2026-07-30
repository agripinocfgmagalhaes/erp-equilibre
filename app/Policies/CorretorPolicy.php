<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Corretor;
use Illuminate\Auth\Access\HandlesAuthorization;

class CorretorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Corretor');
    }

    public function view(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('View:Corretor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Corretor');
    }

    public function update(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('Update:Corretor');
    }

    public function delete(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('Delete:Corretor');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Corretor');
    }

    public function restore(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('Restore:Corretor');
    }

    public function forceDelete(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('ForceDelete:Corretor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Corretor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Corretor');
    }

    public function replicate(AuthUser $authUser, Corretor $corretor): bool
    {
        return $authUser->can('Replicate:Corretor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Corretor');
    }

}