<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

final class ListEvents extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = EventResource::class;

    public function getTabs(): array
    {
        return [
            'Tous' => Tab::make()
                ->badge(Event::query()->count()),
            'Passés' => Tab::make()
                ->badge(Event::query()->where('date', '<', now())->count())
                ->badgeColor(Color::Red)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('date', '<', now())),
            'Courents' => Tab::make()
                ->badgeColor(Color::Blue)
                ->badge(Event::query()->where('date', '=', now())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('date', '>=', now())),
            'Prochains' => Tab::make()
                ->badgeColor(Color::Green)
                ->badge(Event::query()->where('date', '>=', now())->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('date', '>=', now())->orderBy('date', 'asc')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return EventResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('Ajouter un événement'),
        ];
    }
}
