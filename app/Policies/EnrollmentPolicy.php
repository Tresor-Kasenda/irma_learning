<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

final class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->id === $enrollment->user_id || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isStudent() || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->isSuperAdmin();
    }
}
