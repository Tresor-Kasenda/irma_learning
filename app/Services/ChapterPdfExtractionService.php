<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final readonly class ChapterPdfExtractionService
{
    public function __construct(
        private DocumentConversionService        $conversionService,
        private ReadingDurationCalculatorService $durationService
    )
    {
    }

    /**
     * Extract PDF content and update form fields
     *
     * @param mixed $pdfFile The PDF file to extract (TemporaryUploadedFile or path)
     * @param callable $set Filament Set callable function(string $key, mixed $value)
     */
    public function extractAndSetFormData(mixed $pdfFile, callable $set): void
    {
        try {
            $this->validatePdfFile($pdfFile);

            $filePath = $this->getFilePath($pdfFile);
            $originalFileName = $this->getOriginalFileName($pdfFile);

            $result = $this->extractContent($filePath, $originalFileName);
            $duration = $this->calculateDuration($result['content']);

            $this->setFormFields($set, $result, $duration, $originalFileName);

            $this->sendSuccessNotification($duration);
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Validate that a PDF file was provided
     *
     * @throws Exception
     */
    private function validatePdfFile($pdfFile): void
    {
        if (!$pdfFile) {
            throw new Exception('Aucun fichier PDF fourni.');
        }
    }

    /**
     * Get the file path from the uploaded file
     *
     * @throws Exception
     */
    private function getFilePath($pdfFile): string
    {
        $filePath = '';

        if ($pdfFile instanceof TemporaryUploadedFile) {
            $filePath = $pdfFile->getRealPath();
        }

        if (!$filePath || !file_exists($filePath)) {
            throw new Exception('Impossible de trouver le fichier PDF.');
        }

        return $filePath;
    }

    /**
     * Get the original file name from the uploaded file
     */
    private function getOriginalFileName($pdfFile): ?string
    {
        if ($pdfFile instanceof TemporaryUploadedFile) {
            return pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        }

        return null;
    }

    /**
     * Extract content from PDF file
     *
     * @throws Exception
     */
    private function extractContent(string $filePath, ?string $originalFileName): array
    {
        $result = $this->conversionService->convert($filePath, [
            'generateThumbnail' => true,
            'ignorePageNumbers' => true,
            'skipFirstPage' => false,
            'customTitle' => $originalFileName,
        ]);

        if (empty($result['content'])) {
            throw new Exception('Le contenu extrait est vide.');
        }

        return $result;
    }

    /**
     * Calculate reading duration for the content
     */
    private function calculateDuration(string $content): int
    {
        $readingAnalysis = $this->durationService->calculateReadingDuration(
            $content,
            'average'
        );

        return (int)($readingAnalysis['total_minutes'] ?? 15);
    }

    /**
     * Set form fields with extracted data
     * @throws Exception
     */
    private function setFormFields(callable $set, array $result, int $duration, ?string $originalFileName): void
    {
        try {
            Log::debug('Setting form fields', [
                'title' => $result['title'] ?? $originalFileName ?? 'Document PDF',
                'content_length' => mb_strlen($result['content'] ?? ''),
                'duration' => $duration,
                'has_thumbnail' => !empty($result['thumbnail_path']),
                'has_markdown' => !empty($result['markdown_file']),
            ]);

            // Set fields one by one with error handling
            $set('title', $result['title'] ?? $originalFileName ?? 'Document PDF');

            // Truncate content if too large for better performance
            $content = $result['content'] ?? '';
            if (mb_strlen($content) > 100000) {
                Log::warning('Content too large, truncating', ['original_length' => mb_strlen($content)]);
                $content = mb_substr($content, 0, 100000) . '...[truncated]';
            }
            $set('content', $content);

            $set('duration_minutes', $duration);
            $set('content_type', 'pdf');

            // Only set optional fields if they exist and are valid strings
            if (isset($result['thumbnail_path']) && is_string($result['thumbnail_path']) && $result['thumbnail_path'] !== '') {
                Log::debug('Setting cover_image', ['path' => $result['thumbnail_path']]);
                $set('cover_image', $result['thumbnail_path']);
            }

            if (isset($result['markdown_file']) && is_string($result['markdown_file']) && $result['markdown_file'] !== '') {
                Log::debug('Setting markdown_file', ['path' => $result['markdown_file']]);
                $set('markdown_file', $result['markdown_file']);
            }

            Log::debug('Form fields set successfully');
        } catch (Exception $e) {
            Log::error('Error setting form fields', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Send success notification
     */
    private function sendSuccessNotification(int $duration): void
    {
        Notification::make()
            ->title('Extraction PDF réussie')
            ->body(sprintf(
                'Le contenu a été extrait avec succès. Durée estimée: %d minutes.',
                $duration
            ))
            ->success()
            ->send();
    }

    /**
     * Handle extraction error
     */
    private function handleError(Exception $e): void
    {
        Log::error('Erreur extraction PDF', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        Notification::make()
            ->title('Erreur d\'extraction PDF')
            ->body('Erreur lors de l\'extraction: ' . $e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }
}
