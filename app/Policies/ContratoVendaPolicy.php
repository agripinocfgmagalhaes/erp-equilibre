<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContratoVenda;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContratoVendaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContratoVenda');
    }

    public function view(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('View:ContratoVenda');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContratoVenda');
    }

    public function update(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('Update:ContratoVenda');
    }

    public function delete(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('Delete:ContratoVenda');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContratoVenda');
    }

    public function restore(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('Restore:ContratoVenda');
    }

    public function forceDelete(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('ForceDelete:ContratoVenda');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContratoVenda');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContratoVenda');
    }

    public function replicate(AuthUser $authUser, ContratoVenda $contratoVenda): bool
    {
        return $authUser->can('Replicate:ContratoVenda');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContratoVenda');
    }

}