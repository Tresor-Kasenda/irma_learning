<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\MobileMoneyGateway;
use App\Listeners\LogAuthenticationActivity;
use App\Services\ShwaryMobileMoneyGateway;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MobileMoneyGateway::class, ShwaryMobileMoneyGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn (): Password => Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols());

        Vite::prefetch(concurrency: 3);

        Model::preventLazyLoading(! $this->app->isProduction());

        Event::listen(Login::class, [LogAuthenticationActivity::class, 'onLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'onLogout']);
        Event::listen(Failed::class, [LogAuthenticationActivity::class, 'onFailed']);
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
