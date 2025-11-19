<?php

declare(strict_types=1);

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use App\Models\Section;
use App\Services\ChapterPdfExtractionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ViewSection extends ViewRecord
{
    protected static string $resource = SectionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations de la section')
                    ->schema([
                        Infolists\Components\TextEntry::make('formation.title')
                            ->label('Formation'),
                        Infolists\Components\TextEntry::make('title')
                            ->label('Titre'),
                        Infolists\Components\TextEntry::make('order_position')
                            ->label('Position'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('Aucune description'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statut')
                    ->schema([
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Active')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->suffix(' min')
                            ->placeholder('Non définie'),
                        Infolists\Components\TextEntry::make('chapters_count')
                            ->label('Nombre de chapitres')
                            ->getStateUsing(fn (Section $record): int => $record->chapters()->count()),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(SectionResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Actions\Action::make('addChapter')
                ->label('Ajouter un chapitre')
                ->icon('heroicon-o-plus-circle')
                ->color(Color::Slate)
                ->slideOver()
                ->form([
                    TextInput::make('title')
                        ->label('Titre du chapitre')
                        ->required()
                        ->maxLength(255),

                    Select::make('content_type')
                        ->label('Type de contenu')
                        ->options([
                            'text' => 'Texte',
                            'video' => 'Vidéo',
                            'pdf' => 'PDF',
                        ])
                        ->required()
                        ->default('text')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $set('media_url', null);
                        }),

                    FileUpload::make('video_url')
                        ->label('Vidéo')
                        ->disk('public')
                        ->directory('chapters')
                        ->visibility('public')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['video/*'])
                        ->visible(
                            fn (Get $get) => $get('content_type') === 'video'
                        )
                        ->required(
                            fn (Get $get) => $get('content_type') === 'video'
                        ),

                    FileUpload::make('media_url')
                        ->label('Fichier de contenu')
                        ->disk('public')
                        ->directory('chapters')
                        ->visibility('public')
                        ->preserveFilenames()
                        ->columnSpanFull()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(50 * 1024)
                        ->helperText('Uploadez un PDF pour extraction automatique du contenu.')
                        ->visible(fn (Get $get) => $get('content_type') === 'pdf')
                        ->required(fn (Get $get) => $get('content_type') === 'pdf')
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state) {
                                app(ChapterPdfExtractionService::class)->extractAndSetFormData($state, $set);
                            }
                        })
                        ->afterStateHydrated(function ($component, $state) {
                            if ($state && $component->getContainer()->getOperation() === 'edit') {
                                if (! Storage::disk('public')->exists($state)) {
                                    Notification::make()
                                        ->title('Fichier manquant')
                                        ->body('Le fichier PDF original est introuvable.')
                                        ->warning()
                                        ->send();
                                }
                            }
                        }),

                    RichEditor::make('content')
                        ->label('Contenu principal')
                        ->columnSpanFull()
                        ->helperText('Le contenu sera automatiquement rempli lors de l\'import PDF'),

                    Forms\Components\Hidden::make('cover_image'),

                    Forms\Components\Hidden::make('markdown_file'),

                    Forms\Components\Hidden::make('metadata'),

                    TextInput::make('order_position')
                        ->label('Position du chapitre')
                        ->numeric()
                        ->default(fn () => (($this->record->chapters()->max('order_position') ?? 0) + 1))
                        ->required()
                        ->minValue(1),

                    TextInput::make('duration_minutes')
                        ->label('Durée estimée (minutes)')
                        ->numeric()
                        ->suffix('minutes')
                        ->live()
                        ->default(fn () => $this->calculateChapterDuration())
                        ->helperText('Durée calculée automatiquement (PDF) ou selon la section'),

                    Toggle::make('is_free')
                        ->label('Gratuit (aperçu)')
                        ->inline(false)
                        ->helperText('Ce chapitre sera accessible sans inscription'),

                    Toggle::make('is_active')
                        ->label('Chapitre actif')
                        ->inline(false)
                        ->helperText('Ce chapitre sera visible dans la formation')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $providedPosition = isset($data['order_position']) ? (int) $data['order_position'] : null;
                        $nextPosition = ($this->record->chapters()->max('order_position') ?? 0) + 1;
                        $position = ($providedPosition && $providedPosition > 0) ? $providedPosition : $nextPosition;

                        // Utiliser la durée calculée depuis l'extraction PDF ou calculer
                        $duration = $data['duration_minutes'] ?? $this->calculateChapterDuration();

                        $payload = [
                            'title' => $data['title'],
                            'content_type' => $data['content_type'] ?? 'text',
                            'media_url' => $data['media_url'] ?? null,
                            'video_url' => $data['video_url'] ?? null,
                            'cover_image' => $data['cover_image'] ?? null,
                            'markdown_file' => $data['markdown_file'] ?? null,
                            'content' => $data['content'] ?? null,
                            'order_position' => $position,
                            'duration_minutes' => $duration,
                            'is_free' => ! empty($data['is_free']),
                            'is_active' => ! empty($data['is_active']),
                        ];

                        $this->record->chapters()->create($payload);
                    });

                    $this->record->refresh();

                    Notification::make()
                        ->title('Succès')
                        ->body('Le chapitre a été ajouté à la section avec succès.')
                        ->success()
                        ->send();

                    $this->dispatch('refresh');
                }),
        ];
    }

    protected function calculateChapterDuration(): int
    {
        // Get section duration in minutes
        $sectionDurationMinutes = $this->record->duration ?? 0;

        // Get the number of existing chapters + 1 (for the new chapter being added)
        $totalChapters = $this->record->chapters()->count() + 1;

        // If no duration is set or no chapters, return 0
        if ($sectionDurationMinutes <= 0 || $totalChapters <= 0) {
            return 0;
        }

        // Calculate average duration per chapter
        return (int) round($sectionDurationMinutes / $totalChapters);
    }
}
