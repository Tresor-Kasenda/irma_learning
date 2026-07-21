<?php

declare(strict_types=1);

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\EnsureMcpUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Inspector\Laravel\Middleware\WebRequestMonitoring;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            App\Http\Middleware\HandleInertiaRequests::class,
            Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web([
            HandleCors::class,
        ]);
        $middleware->alias([
            'force.password.change' => ForcePasswordChange::class,
            'check.status' => CheckUserStatus::class,
            'mcp.active' => EnsureMcpUserIsActive::class,
            'admin.access' => EnsureUserIsAdmin::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'paid.access' => App\Http\Middleware\EnsurePaidCourseAccess::class,
        ]);

        $middleware->appendToGroup('web', WebRequestMonitoring::class)
            ->appendToGroup('api', WebRequestMonitoring::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
