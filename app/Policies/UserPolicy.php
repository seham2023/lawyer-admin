<?php

namespace App\Policies;

use App\Models\Qestass\User;
use App\Models\LawyerUser;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Scoped by getEloquentQuery in resources
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // For admin type users (Supervisors/Staff)
        if ($model->type === 'admin') {
            return $user->id === $model->id || $user->id === $model->parent_id;
        }

        // For client users (type = user)
        if ($model->type === 'user') {
            // Admins can see all if required, but logic says lawyers see their attached clients
            return $user->type === 'admin' || \App\Models\LawyerUser::where('lawyer_id', $user->id)->where('user_id', $model->id)->where('user_type', 'client')->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($model->type === 'admin') {
            return $user->id === $model->id || $user->id === $model->parent_id;
        }

        if ($model->type === 'user') {
            return $user->type === 'admin' || \App\Models\LawyerUser::where('lawyer_id', $user->id)->where('user_id', $model->id)->where('user_type', 'client')->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($model->type === 'admin') {
            return $user->id === $model->parent_id;
        }

        if ($model->type === 'user') {
            // For clients, we usually use DetachAction, but if hard deleting:
            return $user->type === 'admin';
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->type === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->type === 'admin';
    }
}
