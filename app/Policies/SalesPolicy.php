<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sales;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sales');
    }

    public function view(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('View:Sales');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sales');
    }

    public function update(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('Update:Sales');
    }

    public function delete(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('Delete:Sales');
    }

    public function restore(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('Restore:Sales');
    }

    public function forceDelete(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('ForceDelete:Sales');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sales');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sales');
    }

    public function replicate(AuthUser $authUser, Sales $sales): bool
    {
        return $authUser->can('Replicate:Sales');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sales');
    }

}