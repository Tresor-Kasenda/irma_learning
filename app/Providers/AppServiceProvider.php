<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $this->configureVite();
        $this->shouldBeStrict();
        $this->configureDates();
        $this->configurePasswordValidation();
        Schema::defaultStringLength(191);

        Blade::directive('markdown', function ($expression) {
            return "<?php echo app('markdown')->toHtml($expression); ?>";
        });

        Blade::directive('markdownSafe', function ($expression) {
            return "<?php echo app('markdown')->toSafeHtml($expression); ?>";
        });

        Blade::directive('md', function ($expression) {
            return "<?php echo app('markdown')->toHtml($expression); ?>";
        });

        Blade::directive('markdownExcerpt', function ($expression) {
            return "<?php echo app('markdown')->excerpt($expression); ?>";
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
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard();
    }

    /**
     * Register any application services.
     */
    public function register(): void {}

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
        Password::defaults(fn () => $this->app->isProduction() ? Password::min(8)->uncompromised() : null);
    }
}
