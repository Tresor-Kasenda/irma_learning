<?php

namespace App\Filament\Resources;

use App\Enums\UserProgressEnum;
use App\Filament\Resources\UserProgressResource\Pages;
use App\Models\Chapter;
use App\Models\Section;
use App\Models\UserProgress;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Illuminate\Database\Eloquent\Builder;

class UserProgressResource extends Resource
{
    protected static ?string $model = UserProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Formation';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trackable_type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'App\\Models\\Chapter' => 'Chapitre',
                        'App\\Models\\Section' => 'Section',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'App\\Models\\Chapter' => 'primary',
                        'App\\Models\\Section' => 'warning',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('trackable.title')
                    ->label('Élément')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'not_started',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'not_started' => 'Non commencé',
                        'in_progress' => 'En cours',
                        'completed' => 'Complété',
                        default => $state,
                    }),

                ProgressBar::make('progress_percentage')
                    ->getStateUsing(function ($record) {
                        return [
                            'value' => $record->progress_percentage ?? 0,
                            'color' => match ($record->status) {
                                'not_started' => 'secondary',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                default => 'secondary',
                            },
                        ];
                    })
                    ->hideProgressValue(),

                Tables\Columns\TextColumn::make('time_spent')
                    ->label('Temps (min)')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Commencé le')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Complété le')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),

                // Colonne pour afficher la formation (via les relations)
                Tables\Columns\TextColumn::make('formation_title')
                    ->label('Formation')
                    ->getStateUsing(function ($record) {
                        if ($record->trackable instanceof Chapter) {
                            return $record->trackable->section->module->formation->title ?? 'N/A';
                        }
                        if ($record->trackable instanceof Section) {
                            return $record->trackable->module->formation->title ?? 'N/A';
                        }
                        return 'N/A';
                    })
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(UserProgressEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('trackable_type')
                    ->label('Type d\'élément')
                    ->options([
                        'App\\Models\\Chapter' => 'Chapitre',
                        'App\\Models\\Section' => 'Section',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('completed')
                    ->label('Éléments complétés')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'completed')),

                Tables\Filters\Filter::make('in_progress')
                    ->label('En cours')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'in_progress')),

                Tables\Filters\Filter::make('not_started')
                    ->label('Non commencés')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'not_started')),

                Tables\Filters\Filter::make('high_progress')
                    ->label('Progression élevée (≥75%)')
                    ->query(fn(Builder $query): Builder => $query->where('progress_percentage', '>=', 75)),
            ])
            ->actions([
                Tables\Actions\Action::make('markAsStarted')
                    ->label('Commencer')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->action(fn(UserProgress $record) => $record->markAsStarted())
                    ->visible(fn(UserProgress $record) => $record->status === UserProgressEnum::IN_PROGRESS),

                Tables\Actions\Action::make('markAsCompleted')
                    ->label('Compléter')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(UserProgress $record) => $record->markAsCompleted())
                    ->visible(fn(UserProgress $record) => $record->status !== UserProgressEnum::COMPLETED),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_mark_completed')
                        ->label('Marquer comme complétés')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->markAsCompleted()),

                    Tables\Actions\BulkAction::make('bulk_mark_started')
                        ->label('Marquer comme commencés')
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->action(fn($records) => $records->each->markAsStarted()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                'user.name',
                'status',
                'trackable_type',
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserProgress::route('/'),
            'view' => Pages\ViewUserProgress::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'completed')->count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'trackable']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'trackable.title'];
    }
}
