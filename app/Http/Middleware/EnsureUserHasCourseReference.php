<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasCourseReference
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user->reference_code) {
            return redirect()->route('courses.access')->with('error',
                'Vous n\'avez pas de code de référence attaché à votre compte. Veuillez acheter un accès au cours.');
        }

        return $next($request);
    }
}
