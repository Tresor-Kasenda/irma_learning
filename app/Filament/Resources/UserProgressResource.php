<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserProgressEnum;
use App\Filament\Resources\UserProgressResource\Pages;
use App\Models\Chapter;
use App\Models\Section;
use App\Models\UserProgress;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use UnitEnum;

final class UserProgressResource extends Resource
{
    protected static ?string $model = UserProgress::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Formation';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
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
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\\Models\\Chapter' => 'Chapitre',
                        'App\\Models\\Section' => 'Section',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
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
                    ->getStateUsing(fn ($record): string => $record->status->value)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'not_started' => 'Non commencé',
                        'in_progress' => 'En cours',
                        'completed' => 'Complété',
                        default => $state,
                    }),

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
                            return $record->trackable->section->formation->title ?? 'N/A';
                        }
                        if ($record->trackable instanceof Section) {
                            return $record->trackable->formation->title ?? 'N/A';
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
                    ->query(fn (Builder $query): Builder => $query->where('status', 'completed')),

                Tables\Filters\Filter::make('in_progress')
                    ->label('En cours')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'in_progress')),

                Tables\Filters\Filter::make('not_started')
                    ->label('Non commencés')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'not_started')),

                Tables\Filters\Filter::make('high_progress')
                    ->label('Progression élevée (≥75%)')
                    ->query(fn (Builder $query): Builder => $query->where('progress_percentage', '>=', 75)),
            ])
            ->recordActions([
                Action::make('markAsStarted')
                    ->label('Commencer')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->action(fn (UserProgress $record) => $record->markAsStarted())
                    ->visible(fn (UserProgress $record) => $record->status === UserProgressEnum::IN_PROGRESS),

                Action::make('markAsCompleted')
                    ->label('Compléter')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (UserProgress $record) => $record->markAsCompleted())
                    ->visible(fn (UserProgress $record) => $record->status !== UserProgressEnum::COMPLETED),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('bulk_mark_completed')
                        ->label('Marquer comme complétés')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->markAsCompleted()),

                    BulkAction::make('bulk_mark_started')
                        ->label('Marquer comme commencés')
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->action(fn ($records) => $records->each->markAsStarted()),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['user', 'trackable'])
            ->with([
                'trackable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Chapter::class => ['section.formation'],
                    Section::class => ['formation'],
                ]),
            ]);
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
