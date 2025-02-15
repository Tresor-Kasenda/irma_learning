<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;

final class ListBookings extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = BookingResource::class;

    public function getTabs(): array
    {
        return [
            'Tous les Inscrits' => Tab::make()
                ->badge(Booking::query()->count()),
            'Nouvelle inscriptions' => Tab::make()
                ->badge(Booking::query()->where('created_at', '>=', now())->count())
                ->badgeColor(Color::Red)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '<', now())),
            'Inscription Confirmer' => Tab::make()
                ->badgeColor(Color::Blue)
                ->badge(Booking::query()->where('status', '=', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', true)
                    ->orderByDesc('created_at')
                ),
            'Inscription Non confirmer' => Tab::make()
                ->badgeColor(Color::Green)
                ->badge(Booking::query()->where('status', '=', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '=', false)
                    ->orderByDesc('created_at')
                ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('Ajouter une reservation'),
        ];
    }
}
