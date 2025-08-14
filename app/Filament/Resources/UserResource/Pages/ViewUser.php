<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->label('Photo de profil')
                            ->circular()
                            ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),

                        TextEntry::make('name')
                            ->label('Nom complet'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label('Téléphone'),
                    ])
                    ->columns(2),

                Section::make('Compte')
                    ->schema([
                        TextEntry::make('role')
                            ->label('Rôle')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),

                        TextEntry::make('email_verified_at')
                            ->label('Email vérifié le')
                            ->dateTime(),

                        IconEntry::make('must_change_password')
                            ->label('Doit changer le mot de passe')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('formations_count')
                            ->label('Nombre de formations')
                            ->getStateUsing(fn(User $record): int => $record->formations()->count()),

                        TextEntry::make('created_formations_count')
                            ->label('Formations créées')
                            ->getStateUsing(fn(User $record): int => $record->createdFormations()->count()),

                        TextEntry::make('exam_attempts_count')
                            ->label('Tentatives d\'examen')
                            ->getStateUsing(fn(User $record): int => $record->examAttempts()->count()),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(UserResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
