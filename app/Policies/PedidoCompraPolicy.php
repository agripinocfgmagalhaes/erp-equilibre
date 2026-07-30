<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PedidoCompra;
use Illuminate\Auth\Access\HandlesAuthorization;

class PedidoCompraPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PedidoCompra');
    }

    public function view(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('View:PedidoCompra');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PedidoCompra');
    }

    public function update(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('Update:PedidoCompra');
    }

    public function delete(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('Delete:PedidoCompra');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PedidoCompra');
    }

    public function restore(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('Restore:PedidoCompra');
    }

    public function forceDelete(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('ForceDelete:PedidoCompra');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PedidoCompra');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PedidoCompra');
    }

    public function replicate(AuthUser $authUser, PedidoCompra $pedidoCompra): bool
    {
        return $authUser->can('Replicate:PedidoCompra');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PedidoCompra');
    }

}