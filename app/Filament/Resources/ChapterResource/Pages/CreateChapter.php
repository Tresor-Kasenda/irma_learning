<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Enums\ChapterTypeEnum;
use App\Filament\Resources\ChapterResource;
use App\Service\PdfExtractionService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Storage;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quick_pdf_import')
                ->label('Import PDF Rapide')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->form([
                    Section::make('Import PDF')
                        ->schema([
                            FileUpload::make('pdf_file')
                                ->label('Fichier PDF')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(20480)
                                ->required()
                                ->disk('local')
                                ->directory('temp-pdfs')
                                ->visibility('private'),

                            Select::make('section_id')
                                ->label('Section de destination')
                                ->relationship('section', 'title')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Group::make([
                                Toggle::make('extract_images')
                                    ->label('Extraire les images')
                                    ->default(true),

                                Toggle::make('extract_code')
                                    ->label('Détecter le code')
                                    ->default(true),

                                Toggle::make('create_toc')
                                    ->label('Table des matières')
                                    ->default(true),

                                Toggle::make('generate_cover')
                                    ->label('Image de couverture')
                                    ->default(true),

                                Toggle::make('auto_activate')
                                    ->label('Activer automatiquement')
                                    ->default(true),

                                Toggle::make('mark_as_free')
                                    ->label('Marquer comme gratuit')
                                    ->default(false),
                            ])
                                ->columns(2),

                            TextInput::make('order_position')
                                ->label('Position dans la section')
                                ->numeric()
                                ->default(1)
                                ->minValue(1),
                        ])
                ])
                ->action(function (array $data) {
                    $this->quickImportPdf($data);
                })
                ->modalHeading('Import PDF Rapide')
                ->modalDescription('Importez rapidement un PDF en tant que nouveau chapitre')
                ->modalWidth('2xl'),
        ];
    }

    protected function quickImportPdf(array $data): void
    {
        try {
            $pdfFile = $data['pdf_file'];
            $tempPath = Storage::disk('local')->path($pdfFile);

            if (!file_exists($tempPath)) {
                throw new Exception('Fichier PDF introuvable');
            }

            $options = [
                'extractImages' => $data['extract_images'] ?? true,
                'extractCode' => $data['extract_code'] ?? true,
                'createTableOfContents' => $data['create_toc'] ?? true,
                'generateCoverImage' => $data['generate_cover'] ?? true,
                'ignorePageNumbers' => true,
            ];

            $extractionService = app(PdfExtractionService::class);
            $extractedData = $extractionService->extractPdfContent($tempPath, $options);

            $chapterData = [
                'section_id' => $data['section_id'],
                'title' => $extractedData['title'],
                'description' => $extractedData['description'],
                'content' => $extractedData['content'],
                'content_type' => ChapterTypeEnum::PDF,
                'order_position' => $data['order_position'] ?? 1,
                'estimated_duration' => $extractedData['estimated_duration'],
                'is_active' => $data['auto_activate'] ?? true,
                'is_free' => $data['mark_as_free'] ?? false,
                'metadata' => array_merge($extractedData['metadata'], [
                    'original_pdf_path' => $tempPath,
                    'import_date' => now()->toISOString(),
                    'import_options' => $options,
                ]),
            ];

            $chapter = static::getModel()::create($chapterData);

            $message = "Chapitre créé avec succès!\n\n";
            $message .= "📄 Titre: {$extractedData['title']}\n";
            $message .= "⏱️ Durée: {$extractedData['estimated_duration']} min\n";
            $message .= "📊 Pages: {$extractedData['metadata']['pdf_info']['page_count']}\n";

            if (!empty($extractedData['metadata']['extracted_images'])) {
                $imageCount = count($extractedData['metadata']['extracted_images']);
                $message .= "🖼️ Images: {$imageCount}\n";
            }

            Notification::make()
                ->title('Import PDF réussi')
                ->body($message)
                ->success()
                ->duration(8000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->button()
                        ->url(ChapterResource::getUrl('view', ['record' => $chapter]))
                        ->label('Voir le chapitre'),
                    \Filament\Notifications\Actions\Action::make('edit')
                        ->button()
                        ->url(ChapterResource::getUrl('edit', ['record' => $chapter]))
                        ->label('Modifier'),
                ])
                ->send();

            $this->redirect(ChapterResource::getUrl('edit', ['record' => $chapter]));

        } catch (Exception $e) {
            Notification::make()
                ->title('Erreur d\'import PDF')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['extracted_metadata'])) {
            $data['metadata'] = array_merge($data['metadata'] ?? [], $data['extracted_metadata']);
            unset($data['extracted_metadata'], $data['extracted_content'], $data['extraction_preview']);
        }

        unset($data['pdf_file'], $data['extract_images'], $data['extract_code'], $data['create_toc'], $data['generate_cover']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $chapter = $this->record;

        if (!empty($chapter->metadata['pdf_info'])) {
            Bus::dispatch(function () use ($chapter) {
                Schedule::call(function () use ($chapter) {
                    $tempPath = $chapter->metadata['original_pdf_path'] ?? null;
                    if ($tempPath && file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                })->delay(now()->addDay());
            })->delay(now()->addHours(24));

            Log::info('PDF Chapter Import', [
                'chapter_id' => $chapter->id,
                'title' => $chapter->title,
                'pages' => $chapter->metadata['pdf_info']['page_count'] ?? 0,
                'duration' => $chapter->estimated_duration,
                'user_id' => auth()->id(),
            ]);
        }
    }
}
