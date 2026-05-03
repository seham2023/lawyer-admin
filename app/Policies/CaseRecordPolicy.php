<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CaseRecord;
use App\Models\Qestass\User;

class CaseRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CaseRecord');
    }

    public function view(User $user, CaseRecord $caseRecord): bool
    {
        return $user->checkPermissionTo('view CaseRecord');
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CaseRecord');
    }

    public function update(User $user, CaseRecord $caseRecord): bool
    {
        return $user->checkPermissionTo('update CaseRecord');
    }

    public function delete(User $user, CaseRecord $caseRecord): bool
    {
        return $user->checkPermissionTo('delete CaseRecord');
    }

    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete CaseRecord');
    }

    public function restore(User $user, CaseRecord $caseRecord): bool
    {
        return $user->checkPermissionTo('restore CaseRecord');
    }

    public function forceDelete(User $user, CaseRecord $caseRecord): bool
    {
        return $user->checkPermissionTo('force-delete CaseRecord');
    }
}