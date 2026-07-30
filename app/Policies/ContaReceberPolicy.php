<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContaReceber;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContaReceberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContaReceber');
    }

    public function view(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('View:ContaReceber');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContaReceber');
    }

    public function update(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('Update:ContaReceber');
    }

    public function delete(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('Delete:ContaReceber');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContaReceber');
    }

    public function restore(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('Restore:ContaReceber');
    }

    public function forceDelete(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('ForceDelete:ContaReceber');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContaReceber');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContaReceber');
    }

    public function replicate(AuthUser $authUser, ContaReceber $contaReceber): bool
    {
        return $authUser->can('Replicate:ContaReceber');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContaReceber');
    }

}