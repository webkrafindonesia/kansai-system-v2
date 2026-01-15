<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Mutation;
use Illuminate\Auth\Access\HandlesAuthorization;

class MutationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Mutation');
    }

    public function view(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('View:Mutation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Mutation');
    }

    public function update(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('Update:Mutation');
    }

    public function delete(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('Delete:Mutation');
    }

    public function restore(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('Restore:Mutation');
    }

    public function forceDelete(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('ForceDelete:Mutation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Mutation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Mutation');
    }

    public function replicate(AuthUser $authUser, Mutation $mutation): bool
    {
        return $authUser->can('Replicate:Mutation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Mutation');
    }

}