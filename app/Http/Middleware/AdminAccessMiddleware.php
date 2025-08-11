<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Database\Eloquent\Model;

final class AdminAccessMiddleware extends Middleware
{
    /**
     * @param array<string> $guards
     *
     * @throws AuthenticationException
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (!$guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentPanel();

        abort_if(
            $user instanceof FilamentUser ?
                (!$user->canAccessPanel($panel)) : (config('app.env') !== 'local'),
            403,
            'Accès refusé. Vous devez avoir un rôle administrateur pour accéder à cette section.'
        );
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
