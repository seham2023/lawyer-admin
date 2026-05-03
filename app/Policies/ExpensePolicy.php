<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Expense;
use App\Models\Qestass\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Expense');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->checkPermissionTo('view Expense');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Expense');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->checkPermissionTo('update Expense');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->checkPermissionTo('delete Expense');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete Expense');
    }

    public function restore(User $user, Expense $expense): bool
    {
        return $user->checkPermissionTo('restore Expense');
    }

    public function forceDelete(User $user, Expense $expense): bool
    {
        return $user->checkPermissionTo('force-delete Expense');
    }
}