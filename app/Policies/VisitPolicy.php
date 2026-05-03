<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Visit;
use App\Models\Qestass\User;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Visit');
    }

    public function view(User $user, Visit $visit): bool
    {
        return $user->checkPermissionTo('view Visit');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Visit');
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->checkPermissionTo('update Visit');
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->checkPermissionTo('delete Visit');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Visit');
    }

    public function restore(User $user, Visit $visit): bool
    {
        return $user->checkPermissionTo('restore Visit');
    }

    public function forceDelete(User $user, Visit $visit): bool
    {
        return $user->checkPermissionTo('force-delete Visit');
    }
}