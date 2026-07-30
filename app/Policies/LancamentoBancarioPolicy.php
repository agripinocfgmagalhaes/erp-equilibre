<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LancamentoBancario;
use Illuminate\Auth\Access\HandlesAuthorization;

class LancamentoBancarioPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LancamentoBancario');
    }

    public function view(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('View:LancamentoBancario');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LancamentoBancario');
    }

    public function update(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('Update:LancamentoBancario');
    }

    public function delete(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('Delete:LancamentoBancario');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LancamentoBancario');
    }

    public function restore(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('Restore:LancamentoBancario');
    }

    public function forceDelete(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('ForceDelete:LancamentoBancario');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LancamentoBancario');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LancamentoBancario');
    }

    public function replicate(AuthUser $authUser, LancamentoBancario $lancamentoBancario): bool
    {
        return $authUser->can('Replicate:LancamentoBancario');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LancamentoBancario');
    }

}