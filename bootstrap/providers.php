<?php

declare(strict_types=1);

use App\Providers\MarkdownServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    MarkdownServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
];
