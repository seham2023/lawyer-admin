<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Category;
use App\Models\Qestass\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('view-any Category');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('view Category');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('create Category');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('update Category');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('delete Category');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('{{ deleteAnyPermission }}');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('restore Category');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('{{ restoreAnyPermission }}');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('{{ replicatePermission }}');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('{{ reorderPermission }}');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(App\Models\Qestass\User $user, Category $category): bool
    {
        return $user->checkPermissionTo('force-delete Category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('{{ forceDeleteAnyPermission }}');
    }
}
