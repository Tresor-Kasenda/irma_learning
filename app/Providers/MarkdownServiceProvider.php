<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MarkdownService;
use Illuminate\Support\ServiceProvider;

final class MarkdownServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('markdown', function ($app) {
            return new MarkdownService();
        });

        $this->app->alias('markdown', MarkdownService::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/markdown.php' => config_path('markdown.php'),
        ], 'markdown-config');
    }
}
