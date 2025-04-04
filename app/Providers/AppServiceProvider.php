<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        // $this->configureUrl();
        $this->configureVite();
        $this->shouldBeStrict();
        $this->configureDates();
        $this->configurePasswordValidation();
        Schema::defaultStringLength(191);


        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('viewTelescope', function (User $user) {
            return $user->isAdmin();
        });
    }

    public function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    public function configureVite(): void
    {
        Vite::usePrefetchStrategy('aggressive');
    }

    public function shouldBeStrict(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
        Model::unguard();
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn() => $this->app->isProduction() ? Password::min(8)->uncompromised() : null);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function configureUrl(): void
    {
        URL::forceScheme('https');
    }
}
