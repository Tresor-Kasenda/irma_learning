<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ChapterContentDTO;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ChapterContentService
{
    public function __construct(
        private readonly DocumentConversionService $conversionService,
        private readonly ReadingDurationCalculatorService $durationService
    ) {}

    /**
     * Extracts content from a PDF file and returns a DTO.
     *
     * @param string|TemporaryUploadedFile $file
     * @return ChapterContentDTO
     * @throws Exception
     */
    public function extractFromPdf(mixed $file): ChapterContentDTO
    {
        try {
            $filePath = $this->resolveFilePath($file);
            $originalFileName = $this->resolveOriginalFileName($file);

            if (!file_exists($filePath)) {
                throw new Exception('Le fichier PDF est introuvable.');
            }

            // 1. Convert PDF to Markdown/Text
            $result = $this->conversionService->convert($filePath, [
                'generateThumbnail' => true,
                'ignorePageNumbers' => true,
                'customTitle' => $originalFileName,
            ]);

            // 2. Calculate Reading Duration
            $readingAnalysis = $this->durationService->calculateReadingDuration(
                $result['content'],
                'average'
            );

            // 3. Construct DTO
            return new ChapterContentDTO(
                title: $result['title'],
                content: $result['content'],
                durationMinutes: (int) $readingAnalysis['total_minutes'],
                coverImage: $result['thumbnail_path'] ?? null,
                markdownFile: $result['markdown_file'] ?? null,
                description: $result['description'] ?? null
            );

        } catch (Exception $e) {
            Log::error('Chapter content extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Resolves the real file path from the input.
     */
    private function resolveFilePath(mixed $file): string
    {
        if ($file instanceof TemporaryUploadedFile) {
            return $file->getRealPath();
        }

        if (is_string($file)) {
            return $file;
        }

        throw new Exception('Format de fichier non supporté.');
    }

    /**
     * Resolves the original file name if possible.
     */
    private function resolveOriginalFileName(mixed $file): ?string
    {
        if ($file instanceof TemporaryUploadedFile) {
            return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        return null;
    }
}
