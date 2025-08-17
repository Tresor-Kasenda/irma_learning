<?php

namespace App\Filament\Resources;

use App\Enums\ChapterTypeEnum;
use App\Filament\Resources\ChapterResource\Pages;
use App\Models\Chapter;
use App\Service\PdfExtractionService;
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

class ChapterResource extends Resource
{
    protected static ?string $model = Chapter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Chapitres';

    protected static ?string $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.title')
                    ->label('Section')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre du chapitre')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('content_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(ChapterTypeEnum $state): string => match ($state) {
                        'text' => 'success',
                        'video' => 'warning',
                        'audio' => 'danger',
                        'pdf' => 'info',
                        'interactive' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

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

                    Tables\Actions\Action::make('export_pdf')
                        ->label('Exporter en PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->visible(fn($record) => !empty($record->media_url ?? []))
                        ->action(function ($record) {
                            static::exportToPdf($record);
                        })
                        ->tooltip('Exporter le chapitre vers un nouveau PDF'),

                    Tables\Actions\Action::make('re_extract')
                        ->label('Re-extraire du PDF')
                        ->icon('heroicon-o-arrow-path')
                        ->visible(fn($record) => !empty($record->media_url ?? []))
                        ->form([
                            Forms\Components\Toggle::make('extract_images')
                                ->label('Extraire les images')
                                ->default(true),
                            Forms\Components\Toggle::make('extract_code')
                                ->label('Détecter et formater le code')
                                ->default(true),
                            Forms\Components\Toggle::make('create_toc')
                                ->label('Créer une table des matières')
                                ->default(true),
                        ])
                        ->action(function ($record, array $data) {
                            static::reExtractFromPdf($record, $data);
                        })
                        ->tooltip('Re-extraire le contenu avec de nouveaux paramètres'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash'),
                ])
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
                            static::exportMultipleToPdf($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ])
            ])
            ->defaultSort('order_position', 'asc');
    }

    /**
     * Exporte un chapitre vers un PDF
     */
    protected static function exportToPdf($record): void
    {
        try {
            $html = static::convertMarkdownToHtml($record->content);
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
                        ->openUrlInNewTab()
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Source du chapitre')
                    ->schema([
                        Forms\Components\Tabs::make('source_type')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Manuel')
                                    ->schema([
                                        Forms\Components\Select::make('section_id')
                                            ->label('Section')
                                            ->relationship('section', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\Select::make('module_id')
                                                    ->label('Module')
                                                    ->relationship('module', 'title')
                                                    ->required(),
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Titre de la section')
                                                    ->required(),
                                            ]),

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
                                                $set('metadata', []);
                                                $set('media_url', null);
                                            }),

                                        Forms\Components\FileUpload::make('media_url')
                                            ->label('Fichier de contenu')
                                            ->disk('public')
                                            ->directory('chapters')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->columnSpanFull()
                                            ->acceptedFileTypes(['application/pdf', 'video/*'])
                                            ->helperText('Uploadez un PDF ou une vidéo selon le type.')
                                            ->visible(fn(Forms\Get $get) => in_array($get('content_type'), ['pdf', 'video'], true))
                                            ->required(fn(Forms\Get $get) => in_array($get('content_type'), ['pdf', 'video'], true))
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $meta = $get('metadata') ?? [];
                                                $meta['source_file'] = $state;
                                                $set('metadata', $meta);

                                                if ($state) {
                                                    static::extractPdfContent($state, $set, $get);
                                                }
                                            }),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration du contenu')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu principal')
                            ->columnSpanFull()
                            ->helperText('Le contenu sera automatiquement rempli lors de l\'import PDF'),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Métadonnées')
                            ->helperText('Informations supplémentaires (URL vidéo, fichier PDF, données d\'extraction, etc.)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Paramètres')
                    ->schema([
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans la section')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),

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
            // Early return if no file is provided
            if (!$pdfFile) {
                return;
            }

            // First, verify file exists in storage and get its path
            $filePath = null;

            // Try to get file from public disk (where FileUpload stores it)
            if (Storage::disk('public')->exists($pdfFile)) {
                $filePath = Storage::disk('public')->path($pdfFile);
            } // If not found, try alternative paths
            else {
                $potentialPaths = [
                    Storage::disk('local')->path($pdfFile),
                    storage_path('app/public/' . $pdfFile),
                    storage_path('app/public/chapters/' . $pdfFile)
                ];

                foreach ($potentialPaths as $path) {
                    if (file_exists($path)) {
                        $filePath = $path;
                        break;
                    }
                }
            }

            // If file not found anywhere, notify and exit
            if (!$filePath || !file_exists($filePath)) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Impossible de trouver le fichier PDF.')
                    ->danger()
                    ->send();
                return;
            }

            // Store file reference in metadata
            $currentMetadata = $get('metadata') ?? [];
            $currentMetadata['pdf_info'] = array_merge($currentMetadata['pdf_info'] ?? [], [
                'original_path' => $filePath,
                'filename' => basename($pdfFile),
                'storage_path' => $pdfFile,
                'storage_disk' => 'public',
                'uploaded_at' => now()->toDateTimeString(),
            ]);
            $set('metadata', $currentMetadata);

            // Continue with extraction process
            $options = [
                'extractImages' => $get('extract_images') ?? true,
                'extractCode' => $get('extract_code') ?? true,
                'createTableOfContents' => $get('create_toc') ?? true,
                'generateCoverImage' => $get('generate_cover') ?? true,
                'ignorePageNumbers' => true,
            ];

            $extractionService = app(PdfExtractionService::class);
            $extractedData = $extractionService->extractPdfContent($filePath, $options);

            // Set values from extracted data
            $set('title', $extractedData['title']);
            $set('description', $extractedData['description']);
            $set('content', $extractedData['content']);
            $set('duration_minutes', $extractedData['estimated_duration']);
            $set('content_type', 'pdf');

            // Merge extracted metadata with our file info
            $mergedMetadata = array_merge($currentMetadata, $extractedData['metadata']);
            $set('metadata', $mergedMetadata);

            // Create a preview summary
            $preview = "✅ Extraction réussie!\n\n";
            $preview .= "📄 Titre: {$extractedData['title']}\n";
            $preview .= "⏱️ Durée estimée: {$extractedData['estimated_duration']} minutes\n";
            $preview .= "📊 Nombre de pages: {$extractedData['metadata']['pdf_info']['page_count']}\n";

            if (!empty($extractedData['metadata']['extracted_images'])) {
                $imageCount = count($extractedData['metadata']['extracted_images']);
                $preview .= "🖼️ Images extraites: {$imageCount}\n";
            }

            if (!empty($extractedData['metadata']['table_of_contents'])) {
                $tocCount = count($extractedData['metadata']['table_of_contents']);
                $preview .= "📋 Sections détectées: {$tocCount}\n";
            }

            if (!empty($extractedData['metadata']['cover_image'])) {
                $preview .= "🎨 Image de couverture générée\n";
            }

            $preview .= "\n📝 Contenu prêt pour la publication!";

            $set('extraction_preview', $preview);

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

            $set('extraction_preview', '❌ Erreur lors de l\'extraction du PDF');
        }
    }

    /**
     * Re-extrait le contenu à partir du PDF original avec de nouveaux paramètres
     */
    protected static function reExtractFromPdf($record, array $options): void
    {
        try {
            $originalPdfPath = $record->metadata['pdf_info']['original_path'] ?? null;

            if (!$originalPdfPath || !file_exists($originalPdfPath)) {
                throw new Exception('Fichier PDF original introuvable');
            }

            $extractionService = app(PdfExtractionService::class);
            $extractedData = $extractionService->extractPdfContent($originalPdfPath, $options);

            $record->update([
                'content' => $extractedData['content'],
                'duration_minutes' => $extractedData['estimated_duration'],
                'metadata' => array_merge($record->metadata ?? [], $extractedData['metadata']),
            ]);

            Notification::make()
                ->title('Re-extraction réussie')
                ->body('Le contenu a été re-extrait avec succès avec les nouveaux paramètres.')
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur de re-extraction')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->send();
        }
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
                $combinedHtml .= static::convertMarkdownToHtml($record->content);
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
                        ->openUrlInNewTab()
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
            ->with(['section.module.formation']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
