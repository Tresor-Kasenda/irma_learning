<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Filament\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEnrollment extends ViewRecord
{
    protected static string $resource = EnrollmentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations de l\'Inscription')
                    ->schema([
                        TextEntry::make('user.name')->label('Utilisateur'),
                        TextEntry::make('user.email')->label('Email'),
                        TextEntry::make('formation.title')->label('Formation'),
                        TextEntry::make('formation.duration')->label('Durée')->suffix(' heures'),
                    ])
                    ->columns(2),

                Section::make('Statut et Paiement')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state->getLabel())
                            ->color(fn(EnrollmentStatusEnum $state): string => match ($state) {
                                EnrollmentStatusEnum::Suspended => 'warning',
                                EnrollmentStatusEnum::Active => 'primary',
                                EnrollmentStatusEnum::Completed => 'success',
                                EnrollmentStatusEnum::Cancelled => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment_status')
                            ->label('Statut de paiement')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state->getLabel())
                            ->color(fn(EnrollmentPaymentEnum $state): string => match ($state) {
                                EnrollmentPaymentEnum::PENDING => 'warning',
                                EnrollmentPaymentEnum::PAID => 'success',
                                EnrollmentPaymentEnum::FAILED => 'danger',
                                EnrollmentPaymentEnum::REFUNDED => 'secondary',
                                default => 'gray',
                            }),
                        TextEntry::make('amount_paid')->label('Montant payé')->money('EUR'),
                        TextEntry::make('enrollment_date')->label('Date d\'inscription')->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(EnrollmentResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
