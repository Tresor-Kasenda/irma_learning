<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFormation extends EditRecord
{
    protected static string $resource = FormationResource::class;

    protected static ?string $title = "Modifier une formation";

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(FormationResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Actions\Action::make('add_exam')
                ->label('Ajouter un examen')
                ->icon('heroicon-o-plus-circle')
                ->visible(fn() => $this->record->exams()->doesntExist())
                ->slideOver()
                ->form([
                    TextInput::make('title')
                        ->label('Titre de l\'examen')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex: Examen final de la formation'),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->placeholder('Description de l\'examen...'),

                    Textarea::make('instructions')
                        ->label('Instructions')
                        ->rows(3)
                        ->placeholder('Instructions pour passer l\'examen...'),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('duration_minutes')
                                ->label('Durée (minutes)')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(60)
                                ->suffix('min'),

                            TextInput::make('passing_score')
                                ->label('Score de passage (%)')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(70)
                                ->suffix('%'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('max_attempts')
                                ->label('Tentatives maximales')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(3)
                                ->helperText('0 pour illimité'),

                            Toggle::make('randomize_questions')
                                ->label('Questions aléatoires')
                                ->default(false)
                                ->inline(false),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Toggle::make('show_results_immediately')
                                ->label('Afficher résultats immédiatement')
                                ->default(true)
                                ->inline(false),

                            Toggle::make('is_active')
                                ->label('Actif')
                                ->default(true)
                                ->inline(false),
                        ]),

                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('available_from')
                                ->label('Disponible à partir de')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->seconds(false),

                            DateTimePicker::make('available_until')
                                ->label('Disponible jusqu\'à')
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->seconds(false)
                                ->after('available_from'),
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        $exam = $this->record->exams()->create([
                            'title' => $data['title'],
                            'description' => $data['description'] ?? null,
                            'instructions' => $data['instructions'] ?? null,
                            'duration_minutes' => $data['duration_minutes'],
                            'passing_score' => $data['passing_score'],
                            'max_attempts' => $data['max_attempts'],
                            'randomize_questions' => $data['randomize_questions'] ?? false,
                            'show_results_immediately' => $data['show_results_immediately'] ?? true,
                            'is_active' => $data['is_active'] ?? true,
                            'available_from' => $data['available_from'] ?? null,
                            'available_until' => $data['available_until'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Examen créé avec succès')
                            ->body("L'examen \"{$exam->title}\" a été créé pour la formation.")
                            ->success()
                            ->send();

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Erreur lors de la création')
                            ->body('Impossible de créer l\'examen. Veuillez réessayer.')
                            ->danger()
                            ->send();

                        throw $e;
                    }
                })
                ->successNotificationTitle('Examen créé')
                ->modalWidth('3xl'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Formation saved')
            ->body('The formation has been successfully updated.');
    }
}
