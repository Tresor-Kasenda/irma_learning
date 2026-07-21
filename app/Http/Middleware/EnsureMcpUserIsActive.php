<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMcpUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->status !== UserStatusEnum::ACTIVE) {
            return response()->json([
                'message' => 'Ce compte ne peut pas accéder au serveur MCP.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
