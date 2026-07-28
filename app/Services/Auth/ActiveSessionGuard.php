<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ActiveSessionGuard
{
    /**
     * Determine whether the given user already has another non-expired session.
     */
    public function hasActiveSessionElsewhere(User $user, string $currentSessionId): bool
    {
        return DB::table(config()->string('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->where('last_activity', '>=', now()->subMinutes(config()->integer('session.lifetime'))->getTimestamp())
            ->exists();
    }
}
