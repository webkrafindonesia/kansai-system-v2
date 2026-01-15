<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Assembly;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssemblyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Assembly');
    }

    public function view(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('View:Assembly');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Assembly');
    }

    public function update(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('Update:Assembly');
    }

    public function delete(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('Delete:Assembly');
    }

    public function restore(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('Restore:Assembly');
    }

    public function forceDelete(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('ForceDelete:Assembly');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Assembly');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Assembly');
    }

    public function replicate(AuthUser $authUser, Assembly $assembly): bool
    {
        return $authUser->can('Replicate:Assembly');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Assembly');
    }

}