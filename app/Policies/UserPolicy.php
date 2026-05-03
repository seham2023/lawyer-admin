<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Qestass\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any User');
    }

    public function view(User $user, User $model): bool
    {
        return $user->checkPermissionTo('view User');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create User');
    }

    public function update(User $user, User $model): bool
    {
        return $user->checkPermissionTo('update User');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->checkPermissionTo('delete User');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete User');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->checkPermissionTo('restore User');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->checkPermissionTo('force-delete User');
    }
}