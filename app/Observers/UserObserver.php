<?php

namespace App\Observers;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Notifications\UserAccountCreated;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->role !== UserRoleEnum::STUDENT) {
            $user->notify(new UserAccountCreated(
                name: $user->name,
                email: $user->email,
                password: $user->password
            ));
        }
    }
}
