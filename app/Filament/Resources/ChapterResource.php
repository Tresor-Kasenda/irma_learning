<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ChapterTypeEnum;
use App\Filament\Resources\ChapterResource\Pages;
use App\Models\Chapter;
use App\Models\Section;
use App\Services\DocumentConversionService;
use App\Services\ReadingDurationCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Chapitres';

    protected static ?string $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->groupBy('section'))
            ->columns([
                TextColumn::make('section.title')
                    ->label('Section')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('title')
                    ->label('Titre du chapitre')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('content_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(ChapterTypeEnum $state): string => match ($state) {
                        ChapterTypeEnum::TEXT => 'success',
                        ChapterTypeEnum::VIDEO => 'warning',
                        ChapterTypeEnum::AUDIO => 'danger',
                        ChapterTypeEnum::PDF => 'info',
                        ChapterTypeEnum::INTERACTIVE => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('duration_minutes')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 15 => 'success',
                        $state <= 30 => 'warning',
                        $state <= 60 => 'info',
                        default => 'danger',
                    })
                    ->formatStateUsing(function (int $state): string {
                        $hours = floor($state / 60);
                        $minutes = $state % 60;

                        if ($hours > 0) {
                            return "{$hours}h {$minutes}min";
                        }

                        return "{$minutes} min";
                    }),

                Tables\Columns\IconColumn::make('is_free')
                    ->label('Gratuit')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section')
                    ->label('Section')
                    ->relationship('section', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('content_type')
                    ->label('Type de contenu')
                    ->options([
                        'text' => 'Texte',
                        'video' => 'Vidéo',
                        'audio' => 'Audio',
                        'pdf' => 'PDF',
                        'interactive' => 'Interactif',
                    ]),

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('Gratuit')
                    ->boolean(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actif uniquement')
                    ->falseLabel('Inactif uniquement'),

                Tables\Filters\TernaryFilter::make('from_pdf')
                    ->label('Source PDF')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('metadata->pdf_info'),
                        false: fn(Builder $query) => $query->whereNull('metadata->pdf_info'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\Action::make('recalculate_duration')
                        ->label('Recalculer durée')
                        ->icon('heroicon-o-clock')
                        ->visible(fn($record) => !empty($record->content))
                        ->form([
                            Forms\Components\Select::make('reading_level')
                                ->label('Niveau de lecture')
                                ->options([
                                    'beginner' => 'Débutant (150 mots/min)',
                                    'average' => 'Moyen (200 mots/min)',
                                    'advanced' => 'Avancé (250 mots/min)',
                                    'professional' => 'Professionnel (300 mots/min)',
                                ])
                                ->default('average')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            ChapterResource::recalculateReadingDuration($record, $data['reading_level']);
                        }),

                    Tables\Actions\Action::make('export_pdf')
                        ->label('Exporter en PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->visible(fn($record) => !empty($record->media_url ?? []))
                        ->action(function ($record) {
                            ChapterResource::exportToPdf($record);
                        })
                        ->tooltip('Exporter le chapitre vers un nouveau PDF'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash'),

                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Activer/Désactiver')
                        ->icon('heroicon-o-power')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('toggle_free')
                        ->label('Gratuit/Payant')
                        ->icon('heroicon-o-currency-dollar')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_free' => !$record->is_free]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('export_multiple_pdf')
                        ->label('Exporter en PDF (Sélection)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            ChapterResource::exportMultipleToPdf($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('order_position', 'asc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Source du chapitre')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->label('Section')
                            ->relationship('section', 'title')
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn($record) => mb_strlen($record->title) > 50
                                ? mb_substr($record->title, 0, 50) . '...'
                                : $record->title
                            )
                            ->extraAttributes(function ($get) {
                                $sectionId = $get('section_id');
                                if ($sectionId) {
                                    $formation = Section::find($sectionId);

                                    return $formation ? ['title' => $formation->title] : [];
                                }

                                return [];
                            })
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Titre du chapitre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('content_type')
                            ->label('Type de contenu')
                            ->options([
                                'text' => 'Texte',
                                'video' => 'Vidéo',
                                'audio' => 'Audio',
                                'pdf' => 'PDF',
                                'interactive' => 'Interactif',
                            ])
                            ->required()
                            ->default('text')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('media_url', null);
                            }),

                        Forms\Components\FileUpload::make('media_url')
                            ->label('Fichier de contenu')
                            ->disk('public')
                            ->directory('chapters')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(50 * 1024)
                            ->helperText('Uploadez un PDF pour extraction automatique du contenu.')
                            ->visible(fn(Forms\Get $get) => $get('content_type') === 'pdf')
                            ->required(fn(Forms\Get $get) => $get('content_type') === 'pdf')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    ChapterResource::extractPdfContent($state, $set, $get);
                                }
                            })
                            ->afterStateHydrated(function ($component, $state) {
                                if ($state && $component->getContainer()->getOperation() === 'edit') {
                                    if (!Storage::disk('public')->exists($state)) {
                                        Notification::make()
                                            ->title('Fichier manquant')
                                            ->body('Le fichier PDF original est introuvable.')
                                            ->warning()
                                            ->send();
                                    }
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration du contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu principal')
                            ->columnSpanFull()
                            ->helperText('Le contenu sera automatiquement rempli lors de l\'import PDF'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position du chapitre')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function (Forms\Get $get) {
                                $sectionId = $get('section_id');
                                if ($sectionId) {
                                    return (Chapter::where('section_id', $sectionId)->max('order_position') ?? 0) + 1;
                                }
                                return 1;
                            })
                            ->helperText('Position automatique (prochaine disponible dans cette section)'),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Durée estimée (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->default(15)
                            ->helperText('Sera calculée automatiquement lors de l\'import PDF'),

                        Forms\Components\Toggle::make('is_free')
                            ->label('Gratuit (aperçu)')
                            ->inline(false)
                            ->helperText('Ce chapitre sera accessible sans inscription'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Chapitre actif')
                            ->inline(false)
                            ->helperText('Ce chapitre sera visible dans la formation')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Extrait le contenu du PDF et met à jour les champs du formulaire
     */
    protected static function extractPdfContent($pdfFile, Forms\Set $set, Forms\Get $get): void
    {
        try {
            if (!$pdfFile) {
                return;
            }
            $filePath = '';

            if ($pdfFile instanceof TemporaryUploadedFile) {
                $filePath = $pdfFile->getRealPath();
                $permanentPath = $pdfFile->store('chapters', 'public');

                $pdfFile = $filePath;
            }

            if (!$filePath || !file_exists($filePath)) {
                throw new Exception('Impossible de trouver le fichier PDF.');
            }

            if (!$filePath || !file_exists($filePath)) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Impossible de trouver le fichier PDF.')
                    ->danger()
                    ->send();

                return;
            }

            $extractionService = app(DocumentConversionService::class);
            $result = $extractionService->convert($filePath, [
                'extractImages' => true,
                'extractTables' => true,
                'extractFormulas' => true,
                'extractCode' => true,
                'generateTOC' => true,
                'ignorePageNumbers' => true,
            ]);

            $extractedData = [
                'title' => $result['title'],
                'description' => $result['description'],
                'content' => $result['content'],
                'estimated_duration' => $result['estimated_duration'],
            ];

            $durationService = app(ReadingDurationCalculatorService::class);
            $readingAnalysis = $durationService->calculateReadingDuration(
                $extractedData['content'],
                'average' // Niveau par défaut
            );

            $multiLevelAnalysis = $durationService->getMultiLevelEstimation(
                $extractedData['content'],
            );

            $set('title', $extractedData['title']);
            $set('description', $extractedData['description']);
            $set('content', $extractedData['content']);
            $set('duration_minutes', $readingAnalysis['total_minutes']); // Durée calculée précisément
            $set('content_type', 'pdf');

            Notification::make()
                ->title('Extraction PDF réussie')
                ->body("Le contenu a été extrait avec succès. Durée estimée: {$extractedData['estimated_duration']} minutes.")
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur d\'extraction PDF')
                ->body('Erreur lors de l\'extraction: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function recalculateReadingDuration($record, string $level = 'average'): void
    {
        try {
            if (empty($record->content)) {
                throw new Exception('Aucun contenu à analyser');
            }

            $durationService = app(ReadingDurationCalculatorService::class);
            $readingAnalysis = $durationService->calculateReadingDuration(
                $record->content,
                $record->metadata ?? [],
                $level
            );

            $durationService->getMultiLevelEstimation(
                $record->content,
                $record->metadata ?? []
            );

            $record->update([
                'duration_minutes' => $readingAnalysis['total_minutes']
            ]);

            Notification::make()
                ->title('Durée recalculée')
                ->body("Nouvelle durée estimée: {$readingAnalysis['total_minutes']} minutes (niveau {$level})")
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur de calcul')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Exporte un chapitre vers un PDF
     */
    protected static function exportToPdf($record): void
    {
        try {
            $html = self::convertMarkdownToHtml($record->content);
            $pdf = Pdf::loadHTML($html);

            $filename = 'chapitre-' . $record->id . '-' . time() . '.pdf';
            $path = storage_path('app/public/exports/' . $filename);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            Notification::make()
                ->title('Export réussi')
                ->body("Le chapitre a été exporté: {$filename}")
                ->success()
                ->actions([
                    Action::make('download')
                        ->button()
                        ->url(asset('storage/exports/' . $filename))
                        ->openUrlInNewTab(),
                ])
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur d\'export')
                ->body('Erreur lors de l\'export PDF: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Convertit le markdown en HTML pour l'export PDF
     */
    protected static function convertMarkdownToHtml(string $markdown): string
    {
        $html = $markdown;

        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);

        $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);

        $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code class="$1">$2</code></pre>', $html);

        $html = preg_replace('/\n\n/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';

        $css = '
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 40px; }
                h1, h2, h3 { color: #333; margin-top: 30px; }
                pre { background: #f4f4f4; padding: 15px; border-radius: 5px; }
                code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
                img { max-width: 100%; height: auto; }
            </style>';

        return $css . $html;
    }

    /**
     * Exporte plusieurs chapitres en un seul PDF
     */
    protected static function exportMultipleToPdf($records): void
    {
        try {
            $combinedHtml = '';

            foreach ($records as $record) {
                $combinedHtml .= '<h1>' . e($record->title) . '</h1>';
                $combinedHtml .= self::convertMarkdownToHtml($record->content);
                $combinedHtml .= '<div style="page-break-after: always;"></div>';
            }

            $pdf = Pdf::loadHTML($combinedHtml);
            $filename = 'chapitres-combines-' . time() . '.pdf';
            $path = storage_path('app/public/exports/' . $filename);

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            Notification::make()
                ->title('Export combiné réussi')
                ->body("Les chapitres ont été exportés: {$filename}")
                ->success()
                ->actions([
                    Action::make('download')
                        ->button()
                        ->url(asset('storage/exports/' . $filename))
                        ->openUrlInNewTab(),
                ])
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur d\'export combiné')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->send();
        }
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
            'index' => Pages\ListChapters::route('/'),
            'create' => Pages\CreateChapter::route('/create'),
            'view' => Pages\ViewChapter::route('/{record}/show'),
            'edit' => Pages\EditChapter::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['section.formation']);
    }
}
