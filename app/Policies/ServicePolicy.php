<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Service;
use App\Models\Qestass\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Service');
    }

    public function view(User $user, Service $service): bool
    {
        return $user->checkPermissionTo('view Service');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Service');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->checkPermissionTo('update Service');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->checkPermissionTo('delete Service');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Service');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->checkPermissionTo('restore Service');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->checkPermissionTo('force-delete Service');
    }
}