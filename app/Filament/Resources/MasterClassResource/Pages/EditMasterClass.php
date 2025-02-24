<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\Pages;

use App\Filament\Resources\MasterClassResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

final class EditMasterClass extends EditRecord
{
    protected static string $resource = MasterClassResource::class;

    public function getHeading(): string|Htmlable
    {
        return 'Modifier le cours';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Supprimer le cours')
                ->icon('heroicon-o-trash'),
            Actions\Action::make('Formation continue')
                ->label('Ajouter une formation')
                ->icon('heroicon-o-plus-circle')
                ->modalWidth(MaxWidth::ThreeExtraLarge)
                ->form([
                    Section::make('Formation')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->label('Titre de la formation')
                                ->required()
                                ->placeholder("Titre de la formation")
                                ->maxLength(255),
                            DatePicker::make('completed_at')
                                ->label("Date de fin de la formation")
                                ->required()
                                ->native(false)
                                ->placeholder("Choisir une date"),
                            FileUpload::make('images')
                                ->directory('images')
                                ->label('Couverture')
                                ->downloadable()
                                ->previewable()
                                ->maxSize(10240)
                                ->deletable()
                                ->uploadingMessage('Uploading images...')
                                ->columnSpanFull(),
                            FileUpload::make('path')
                                ->directory('formations')
                                ->label('Contenu (PDF)')
                                ->downloadable()
                                ->previewable()
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240) // Taille maximale de 10MB
                                ->deletable()
                                ->uploadingMessage('Uploading path...')
                                ->columnSpanFull(),
                            RichEditor::make('content')
                                ->label('Introduction')
                                ->fileAttachmentsDirectory('trainings')
                                ->columnSpanFull()
                                ->disableGrammarly(),
                            RichEditor::make('description')
                                ->label('Description')
                                ->fileAttachmentsDirectory('trainings')
                                ->columnSpanFull()
                                ->disableGrammarly(),
                            Toggle::make('is_completed')
                                ->inline()
                                ->label('Definir comme dernier'),
                        ]),
                ])
                ->slideOver()
                ->action(function (array $data): void {
                    $this->getRecord()->trainings()->create([
                        'title' => $data['title'],
                        'completed_at' => $data['completed_at'],
                        'path' => $data['path'],
                        'content' => $data['content'],
                        'description' => $data['description'],
                        'is_completed' => $data['is_completed'],
                    ]);

                    Notification::make()
                        ->success()
                        ->title("Formation ajoutée")
                        ->body("La formation a été ajoutée avec succès")
                        ->send();
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Modification du cours')
            ->body('Le cours a ete modifier avec success');
    }
}
