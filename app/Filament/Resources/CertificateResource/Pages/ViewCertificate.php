<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use App\Filament\Resources\CertificateResource;
use App\Models\Certificate;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCertificate extends ViewRecord
{
    protected static string $resource = CertificateResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations du Certificat')
                    ->schema([
                        TextEntry::make('certificate_number')
                            ->label('Numéro de certificat')
                            ->copyable(),
                        TextEntry::make('verification_hash')
                            ->label('Hash de vérification')
                            ->copyable()
                            ->limit(50),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'success',
                                'revoked' => 'danger',
                                'suspended' => 'warning',
                            }),
                        TextEntry::make('is_valid')
                            ->label('Valide')
                            ->getStateUsing(fn(Certificate $record) => $record->isValid() ? 'Oui' : 'Non')
                            ->color(fn(Certificate $record) => $record->isValid() ? 'success' : 'danger'),
                    ])
                    ->columns(2),

                Section::make('Bénéficiaire et Formation')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Utilisateur'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('formation.title')
                            ->label('Formation'),
                        TextEntry::make('formation.category')
                            ->label('Catégorie'),
                    ])
                    ->columns(2),

                Section::make('Performance')
                    ->schema([
                        TextEntry::make('final_score')
                            ->label('Score final')
                            ->suffix('%')
                            ->color(fn($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                        TextEntry::make('issue_date')
                            ->label('Date d\'émission')
                            ->dateTime(),
                        TextEntry::make('expiry_date')
                            ->label('Date d\'expiration')
                            ->dateTime()
                            ->placeholder('Aucune expiration'),
                    ])
                    ->columns(2),

                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('Informations additionnelles')
                            ->listWithLineBreaks()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Certificate $record) => !empty($record->metadata)),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
