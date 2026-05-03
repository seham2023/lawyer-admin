<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ExpenseCheck;
use App\Models\Qestass\User;

class ExpenseCheckPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ExpenseCheck');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('view ExpenseCheck');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ExpenseCheck');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('update ExpenseCheck');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('delete ExpenseCheck');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('{{ deleteAnyPermission }}');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('restore ExpenseCheck');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('{{ restoreAnyPermission }}');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('{{ replicatePermission }}');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('{{ reorderPermission }}');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExpenseCheck $expensecheck): bool
    {
        return $user->checkPermissionTo('force-delete ExpenseCheck');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('{{ forceDeleteAnyPermission }}');
    }
}
