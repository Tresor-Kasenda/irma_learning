<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatusEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $status Optional specific status to check
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $status = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($status) {
            $requiredStatus = UserStatusEnum::from($status);

            if ($user->status !== $requiredStatus) {
                return $this->handleUnauthorizedAccess($user->status);
            }
        }

        if ($user->status === UserStatusEnum::INACTIVE) {
            return redirect()->route('account.inactive');
        }

        if ($user->status === UserStatusEnum::BANNED) {
            return redirect()->route('account.suspended');
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access based on user status.
     */
    private function handleUnauthorizedAccess(UserStatusEnum $status): Response
    {
        return match ($status) {
            UserStatusEnum::INACTIVE => redirect()->route('account.inactive'),
            UserStatusEnum::BANNED => redirect()->route('account.suspended'),
            default => response()->json([
                'message' => 'Unauthorized access due to user status.',
                'status' => $status->getLabel(),
            ], 403),
        };
    }
}
