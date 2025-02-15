<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Widgets;

use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Models\Event;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class EventStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListEvents::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Tous les events', Event::count())
                ->color('success'),
            Stat::make('Events passés', Event::where('date', '<', now())->count())
                ->color('secondary'),
            Stat::make('Events courents', Event::where('date', '=', now())->count())
                ->color('secondary'),
            Stat::make('Event a venir', Event::where('date', '>=', now())->count())
                ->color('danger'),
        ];
    }
}
