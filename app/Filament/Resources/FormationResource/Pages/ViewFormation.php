<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormationResource\Pages;

use App\Enums\FormationLevelEnum;
use App\Filament\Resources\FormationResource;
use App\Models\Formation;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;

final class ViewFormation extends ViewRecord
{
    protected static string $resource = FormationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre'),
                        ImageEntry::make('image')
                            ->size('xl')
                            ->label('Image'),
                        TextEntry::make('short_description')
                            ->label('Description courte')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Détails de formation')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Prix')
                            ->money('EUR'),
                        TextEntry::make('duration_hours')
                            ->label('Durée (heures)')
                            ->suffix(' h'),
                        TextEntry::make('difficulty_level')
                            ->label('Niveau de difficulté')
                            ->badge()
                            ->formatStateUsing(fn(FormationLevelEnum $state): string => $state->getLabel())
                            ->color(fn(FormationLevelEnum $state): string => match ($state) {
                                FormationLevelEnum::BEGINNER => 'success',
                                FormationLevelEnum::INTERMEDIATE => 'warning',
                                FormationLevelEnum::ADVANCED => 'danger',
                            }),
                        TextEntry::make('certification_threshold')
                            ->label('Seuil de certification')
                            ->suffix('%'),
                        TextEntry::make('language')
                            ->label('Langue'),
                        TextEntry::make('tags')
                            ->label('Tags')
                            ->badge()
                            ->separator(',')
                            ->getStateUsing(
                                fn(Formation $record): array => is_string($record->tags) ? json_decode($record->tags, true) ?? [] : ($record->tags ?? [])
                            ),
                    ])
                    ->columns(3),

                Section::make('Statut')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Actif')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Actif' : 'Inactif')
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('is_featured')
                            ->label('Mis en avant')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Oui' : 'Non')
                            ->color(fn(bool $state): string => $state ? 'warning' : 'gray'),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(FormationResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),

            Actions\Action::make('addSections')
                ->label('Ajouter une section')
                ->icon('heroicon-o-plus-circle')
                ->color(Color::Slate)
                ->slideOver()
                ->form([
                    TextInput::make('title')
                        ->required()
                        ->placeholder('Titre de la section')
                        ->maxLength(255)
                        ->unique('sections'),
                    Textarea::make('description')
                        ->rows(3)
                        ->autosize()
                        ->maxLength(65535)
                        ->placeholder('Description de la section')
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('order_position')
                                ->label('Position')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->default(fn() => ($this->record->sections()->max('order_position') ?? 0) + 1)
                                ->helperText('Position automatique (prochaine disponible)'),

                            TextInput::make('duration')
                                ->label('Durée estimée (minutes)')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->default(fn() => $this->calculateSectionDuration())
                                ->helperText('Durée calculée automatiquement selon la formation'),
                        ]),

                    Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Module actif et visible pour les étudiants'),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $nextPosition = ($this->record->sections()->max('order_position') ?? 0) + 1;

                        $calculatedDuration = $this->calculateSectionDuration();

                        $payload = [
                            'title' => $data['title'],
                            'description' => $data['description'] ?? null,
                            'order_position' => $nextPosition,
                            'duration' => $calculatedDuration,
                            'is_active' => !empty($data['is_active']),
                        ];

                        $this->record->sections()->create($payload);
                    });

                    $this->record->refresh();

                    Notification::make()
                        ->title('Section ajoutée avec succès')
                        ->body('La section a été ajoutée à la formation avec succès.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function calculateSectionDuration(): int
    {
        // Convert formation duration from hours to minutes
        $formationDurationMinutes = ($this->record->duration_hours ?? 0) * 60;

        // Get the number of existing sections + 1 (for the new section being added)
        $totalSections = $this->record->sections()->count() + 1;

        // If no duration is set or no sections, return 0
        if ($formationDurationMinutes <= 0 || $totalSections <= 0) {
            return 0;
        }

        // Calculate average duration per section
        return (int)round($formationDurationMinutes / $totalSections);
    }
}
