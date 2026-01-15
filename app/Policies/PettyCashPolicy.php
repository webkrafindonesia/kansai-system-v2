<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PettyCash;
use Illuminate\Auth\Access\HandlesAuthorization;

class PettyCashPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PettyCash');
    }

    public function view(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('View:PettyCash');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PettyCash');
    }

    public function update(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('Update:PettyCash');
    }

    public function delete(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('Delete:PettyCash');
    }

    public function restore(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('Restore:PettyCash');
    }

    public function forceDelete(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('ForceDelete:PettyCash');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PettyCash');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PettyCash');
    }

    public function replicate(AuthUser $authUser, PettyCash $pettyCash): bool
    {
        return $authUser->can('Replicate:PettyCash');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PettyCash');
    }

}