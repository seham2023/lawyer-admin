<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Client;
use App\Models\Qestass\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('view-any Client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(App\Models\Qestass\User $user, Client $client): bool
    {
        return $user->checkPermissionTo('view Client');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('create Client');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(App\Models\Qestass\User $user, Client $client): bool
    {
        return $user->checkPermissionTo('update Client');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(App\Models\Qestass\User $user, Client $client): bool
    {
        return $user->checkPermissionTo('delete Client');
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
    public function restore(App\Models\Qestass\User $user, Client $client): bool
    {
        return $user->checkPermissionTo('restore Client');
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
    public function replicate(App\Models\Qestass\User $user, Client $client): bool
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
    public function forceDelete(App\Models\Qestass\User $user, Client $client): bool
    {
        return $user->checkPermissionTo('force-delete Client');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(App\Models\Qestass\User $user): bool
    {
        return $user->checkPermissionTo('{{ forceDeleteAnyPermission }}');
    }
}
