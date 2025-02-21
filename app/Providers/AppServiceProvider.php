<?php

declare(strict_types=1);

namespace App\Providers;

use App\Service\PdfConverterService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        // $this->configureUrl();
        //$this->configureVite();
        $this->shouldBeStrict();
        //$this->configureDates();
        $this->configurePasswordValidation();

        $this->app->singleton(PdfConverterService::class, function ($app) {
            return new PdfConverterService;
        });
    }

    public function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    public function shouldBeStrict(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
        Model::unguard();
    }

    /**
     * Configure the password validation rules.
     */
    private function configurePasswordValidation(): void
    {
        Password::defaults(fn() => $this->app->isProduction() ? Password::min(8)->uncompromised() : null);
    }

    public function configureVite(): void
    {
        Vite::usePrefetchStrategy('aggressive');
    }

    public function configureUrl(): void
    {
        URL::forceScheme('https');
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
