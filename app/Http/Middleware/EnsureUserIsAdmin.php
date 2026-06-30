<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->isAdmin() || $user->isRoot()),
            403,
            'Accès refusé. Vous devez avoir un rôle administrateur pour accéder à cette section.',
        );

        return $next($request);
    }
}
