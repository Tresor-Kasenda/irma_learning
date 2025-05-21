<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAllChaptersCompleted;
use App\Http\Middleware\ForcePasswordChange;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use \Inspector\Laravel\Middleware\WebRequestMonitoring;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            HandleCors::class,
        ]);
        $middleware->alias([
            'force.password.change' => ForcePasswordChange::class,
            'completed.chapters' => EnsureAllChaptersCompleted::class,
        ]);

        $middleware->appendToGroup('web', WebRequestMonitoring::class)
            ->appendToGroup('api', WebRequestMonitoring::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
