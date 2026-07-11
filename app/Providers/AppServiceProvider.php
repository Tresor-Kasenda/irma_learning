<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Model::preventLazyLoading(! $this->app->isProduction());

        Event::listen(Login::class, [LogAuthenticationActivity::class, 'onLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'onLogout']);
        Event::listen(Failed::class, [LogAuthenticationActivity::class, 'onFailed']);
    }
}
