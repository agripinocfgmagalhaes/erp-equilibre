<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContaBancaria;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContaBancariaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContaBancaria');
    }

    public function view(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('View:ContaBancaria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContaBancaria');
    }

    public function update(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('Update:ContaBancaria');
    }

    public function delete(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('Delete:ContaBancaria');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContaBancaria');
    }

    public function restore(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('Restore:ContaBancaria');
    }

    public function forceDelete(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('ForceDelete:ContaBancaria');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContaBancaria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContaBancaria');
    }

    public function replicate(AuthUser $authUser, ContaBancaria $contaBancaria): bool
    {
        return $authUser->can('Replicate:ContaBancaria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContaBancaria');
    }

}