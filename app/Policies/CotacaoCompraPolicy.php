<?php
namespace App\Policies;
use App\Models\CotacaoCompra;
use App\Models\User;

class CotacaoCompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['encarregado', 'responsavel', 'comprador', 'admin']);
    }

    public function view(User $user, CotacaoCompra $cotacao): bool
    {
        return $user->hasAnyRole(['encarregado', 'responsavel', 'comprador', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['encarregado', 'responsavel', 'comprador', 'admin']);
    }

    public function update(User $user, CotacaoCompra $cotacao): bool
    {
        return $user->hasAnyRole(['encarregado', 'responsavel', 'comprador', 'admin']);
    }

    public function delete(User $user, CotacaoCompra $cotacao): bool
    {
        return $user->hasAnyRole(['encarregado', 'responsavel', 'comprador', 'admin']);
    }
}
