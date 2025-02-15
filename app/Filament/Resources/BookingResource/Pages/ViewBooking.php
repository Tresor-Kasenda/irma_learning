<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

final class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make('Informations générales')
                        ->schema([
                            TextEntry::make('title')
                                ->label('Titre de civiliter'),
                            TextEntry::make('name')
                                ->label("Nom de l'agent"),
                            TextEntry::make('firstname')
                                ->label("Prenom de l'agent"),
                            TextEntry::make('email')
                                ->icon('heroicon-m-envelope')
                                ->label('Email'),
                            TextEntry::make('phone_number')
                                ->icon('heroicon-m-phone')
                                ->label('N de telephone'),
                            TextEntry::make('office_phone')
                                ->icon('heroicon-m-phone')
                                ->label('Numero fixe'),
                            TextEntry::make('town')
                                ->label('ville'),
                            IconEntry::make('status')
                                ->boolean()
                                ->trueColor('info')
                                ->falseColor('warning')
                                ->label("Status de l'inscrit"),
                            TextEntry::make('reference')
                                ->copyable()
                                ->label("Reference de l'inscrit"),
                        ])
                        ->columns(2),
                ])
                    ->columnSpan(['lg' => 2]),
                Split::make([
                    Section::make('Informations générales')
                        ->schema([
                            TextEntry::make('event.title')
                                ->label('Événement'),
                            TextEntry::make('company')
                                ->label('Entreprise'),
                            TextEntry::make('sector')
                                ->label("Secteur d'activiter"),
                            TextEntry::make('position')
                                ->label('Votre role'),
                            TextEntry::make('office_phone')
                                ->icon('heroicon-m-phone')
                                ->label('Numero fixe'),
                        ])
                        ->columnSpan(['lg' => 2]),
                ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
