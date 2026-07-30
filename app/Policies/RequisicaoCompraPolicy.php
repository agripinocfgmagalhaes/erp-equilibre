<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RequisicaoCompra;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequisicaoCompraPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RequisicaoCompra');
    }

    public function view(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('View:RequisicaoCompra');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RequisicaoCompra');
    }

    public function update(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('Update:RequisicaoCompra');
    }

    public function delete(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('Delete:RequisicaoCompra');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RequisicaoCompra');
    }

    public function restore(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('Restore:RequisicaoCompra');
    }

    public function forceDelete(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('ForceDelete:RequisicaoCompra');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RequisicaoCompra');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RequisicaoCompra');
    }

    public function replicate(AuthUser $authUser, RequisicaoCompra $requisicaoCompra): bool
    {
        return $authUser->can('Replicate:RequisicaoCompra');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RequisicaoCompra');
    }

}