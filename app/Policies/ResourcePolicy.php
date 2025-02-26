<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(PermissionEnum::VIEW_DASHBOARD);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission(PermissionEnum::MANAGE_CONTENT);
    }

    public function manageUsers(User $user): bool
    {
        return $user->hasPermission(PermissionEnum::MANAGE_USERS);
    }

    public function viewReports(User $user): bool
    {
        return $user->hasPermission(PermissionEnum::VIEW_REPORTS);
    }
}
