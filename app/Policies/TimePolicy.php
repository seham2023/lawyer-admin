<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Qestass\Time;
use App\Models\Qestass\User;

class TimePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Time');
    }

    public function view(User $user, Time $time): bool
    {
        return $user->checkPermissionTo('view Time');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Time');
    }

    public function update(User $user, Time $time): bool
    {
        return $user->checkPermissionTo('update Time');
    }

    public function delete(User $user, Time $time): bool
    {
        return $user->checkPermissionTo('delete Time');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Time');
    }
}
