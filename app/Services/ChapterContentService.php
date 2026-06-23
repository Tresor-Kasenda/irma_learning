<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ChapterContentDTO;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class ChapterContentService
{
    public function __construct(
        private readonly DocumentConversionService $conversionService,
        private readonly ReadingDurationCalculatorService $durationService
    ) {}

    /**
     * Extrait le contenu d'un fichier PDF et retourne un DTO.
     *
     * @param  string|TemporaryUploadedFile  $file  Fichier uploadé ou chemin vers le PDF
     */
    public function extractFromPdf(mixed $file): ChapterContentDTO
    {
        try {
            $filePath = $this->resolveFilePath($file);
            $fallbackTitle = $this->resolveOriginalFileName($file);

            if (! file_exists($filePath)) {
                throw new Exception("Le fichier PDF est introuvable : {$filePath}");
            }

            $result = $this->conversionService->convert($filePath, [
                'generateThumbnail' => true,
                'ignorePageNumbers' => true,
                'skipFirstPage' => false,
                'fallbackTitle' => $fallbackTitle,
            ]);

            $readingAnalysis = $this->durationService->calculateReadingDuration(
                $result['content'],
                'average'
            );

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
     * Résout le chemin absolu du fichier depuis différentes sources possibles.
     *
     * Gère trois cas :
     * 1. Objet TemporaryUploadedFile Livewire  → getRealPath()
     * 2. Chemin absolu déjà valide             → utilisé tel quel
     * 3. Chemin relatif au disque "public"     → converti en chemin absolu via Storage
     */
    private function resolveFilePath(mixed $file): string
    {
        if ($file instanceof TemporaryUploadedFile) {
            return $file->getRealPath();
        }

        if (is_string($file)) {
            if (file_exists($file)) {
                return $file;
            }

            $absolutePath = Storage::disk('public')->path($file);
            if (file_exists($absolutePath)) {
                return $absolutePath;
            }

            return $file;
        }

        throw new Exception('Format de fichier non supporté.');
    }

    private function resolveOriginalFileName(mixed $file): ?string
    {
        if ($file instanceof TemporaryUploadedFile) {
            return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        if (is_string($file)) {
            return pathinfo($file, PATHINFO_FILENAME);
        }

        return null;
    }
}
