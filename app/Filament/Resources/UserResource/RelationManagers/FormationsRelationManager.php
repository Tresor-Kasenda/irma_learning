<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\FormationLevelEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormationsRelationManager extends RelationManager
{
    protected static string $relationship = 'formations';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Formations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('https://via.placeholder.com/40x40?text=F'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Formation')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        FormationLevelEnum::BEGINNER => 'success',
                        FormationLevelEnum::INTERMEDIATE => 'warning',
                        FormationLevelEnum::ADVANCED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\SelectColumn::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\SelectColumn::make('pivot.payment_status')
                    ->label('Paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('pivot.progress_percentage')
                    ->label('Progression')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('pivot.enrollment_date')
                    ->label('Inscrit le')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Niveau')
                    ->options(FormationLevelEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.payment_status')
                    ->label('Statut de paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DetachAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->color('danger'),
                    Tables\Actions\Action::make('view_formation')
                        ->label('Voir formation')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        //->url(fn($record) => route('filament.admin.resources.formations.view', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->color('danger'),
                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Activer/Désactiver')
                        ->icon('heroicon-o-power')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('pivot.enrollment_date', 'desc');
    }
}
