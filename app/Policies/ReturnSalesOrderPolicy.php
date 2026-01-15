<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReturnSalesOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReturnSalesOrderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReturnSalesOrder');
    }

    public function view(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('View:ReturnSalesOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReturnSalesOrder');
    }

    public function update(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('Update:ReturnSalesOrder');
    }

    public function delete(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('Delete:ReturnSalesOrder');
    }

    public function restore(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('Restore:ReturnSalesOrder');
    }

    public function forceDelete(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('ForceDelete:ReturnSalesOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReturnSalesOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReturnSalesOrder');
    }

    public function replicate(AuthUser $authUser, ReturnSalesOrder $returnSalesOrder): bool
    {
        return $authUser->can('Replicate:ReturnSalesOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReturnSalesOrder');
    }

}