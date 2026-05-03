<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Payment;
use App\Models\Qestass\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Payment');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->checkPermissionTo('view Payment');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Payment');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->checkPermissionTo('update Payment');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->checkPermissionTo('delete Payment');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Payment');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->checkPermissionTo('restore Payment');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->checkPermissionTo('force-delete Payment');
    }
}