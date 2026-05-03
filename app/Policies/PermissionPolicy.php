<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Permission;
use App\Models\Qestass\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Permission');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->checkPermissionTo('view Permission');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Permission');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->checkPermissionTo('update Permission');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->checkPermissionTo('delete Permission');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Permission');
    }
}
