<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Chapter;
use App\Services\PythonPdfExtractionService;
use App\Services\ReadingDurationCalculatorService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class ExtractChapterPdf implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 900;

    public bool $failOnTimeout = true;

    /** @var array<int, int> */
    public array $backoff = [60, 180];

    public int $uniqueFor = 600;

    public function __construct(
        public readonly int $chapterId,
        public readonly string $mediaPath,
        public readonly string $contentHash,
    ) {
        $this->onQueue('pdf-extraction');
    }

    public function handle(
        PythonPdfExtractionService $extractionService,
        ReadingDurationCalculatorService $durationService,
    ): void {
        $chapter = Chapter::query()->find($this->chapterId);
        if (! $chapter || $chapter->media_url !== $this->mediaPath || $chapter->content_type->value !== 'pdf') {
            return;
        }

        $chapter->update([
            'processing_status' => 'processing',
            'processing_error' => null,
            'processing_started_at' => now(),
            'processed_at' => null,
        ]);

        $processingMetadata = $chapter->processing_metadata ?? [];
        $retainedAssetDirectories = is_array($processingMetadata['previous_asset_directories'] ?? null)
            ? $processingMetadata['previous_asset_directories']
            : [];
        $previousAssetDirectories = array_values(array_filter([
            $processingMetadata['asset_directory'] ?? null,
            $processingMetadata['previous_asset_directory'] ?? null,
            ...$retainedAssetDirectories,
        ], fn (mixed $directory): bool => is_string($directory) && $directory !== ''));
        $assetDirectory = sprintf(
            'chapters/extracted/%d/%s-%s',
            $chapter->id,
            mb_substr(hash('sha256', $this->mediaPath), 0, 16),
            Str::uuid()->toString(),
        );
        $result = $extractionService->extract(
            Storage::disk('public')->path($this->mediaPath),
            $assetDirectory,
        );

        $markdown = $result['markdown'];
        $markdownFile = $assetDirectory.'/content.md';
        Storage::disk('public')->put($markdownFile, $markdown);

        $currentContentHash = hash('sha256', (string) $chapter->fresh()->content);
        $contentWasEditedDuringProcessing = ! hash_equals($this->contentHash, $currentContentHash);
        $warnings = $result['warnings'];

        if ($contentWasEditedDuringProcessing) {
            $warnings[] = 'Le contenu Markdown a été modifié pendant l’extraction : la version saisie a été conservée.';
        } else {
            foreach ($previousAssetDirectories as $previousAssetDirectory) {
                if ($previousAssetDirectory !== $assetDirectory) {
                    Storage::disk('public')->deleteDirectory($previousAssetDirectory);
                }
            }
        }

        $updates = [
            'cover_image' => $assetDirectory.'/'.$result['cover_file'],
            'markdown_file' => $markdownFile,
            'processing_status' => $result['ocr_required_pages'] === [] ? 'completed' : 'needs_ocr',
            'processing_error' => null,
            'processing_metadata' => [
                'asset_directory' => $assetDirectory,
                'document_type' => $result['document_type'],
                'extraction_strategy' => $result['extraction_strategy'],
                'previous_asset_directories' => $contentWasEditedDuringProcessing ? $previousAssetDirectories : [],
                'page_count' => $result['page_count'],
                'word_count' => $result['word_count'],
                'image_count' => $result['image_count'],
                'visual_pages' => $result['visual_pages'],
                'ocr_pages' => $result['ocr_pages'],
                'ocr_required_pages' => $result['ocr_required_pages'],
                'warnings' => $warnings,
            ],
            'processed_at' => now(),
        ];

        if (! $contentWasEditedDuringProcessing) {
            $updates['content'] = $markdown;
            $updates['duration_minutes'] = (int) $durationService
                ->calculateReadingDuration($markdown, 'average')['total_minutes'];
        }

        $chapter->update($updates);
    }

    public function uniqueId(): string
    {
        return $this->chapterId.':'.$this->mediaPath;
    }

    public function failed(?Throwable $exception): void
    {
        Chapter::query()
            ->whereKey($this->chapterId)
            ->where('media_url', $this->mediaPath)
            ->update([
                'processing_status' => 'failed',
                'processing_error' => $exception?->getMessage() ?? 'L’extraction PDF a échoué.',
                'processed_at' => now(),
            ]);
    }
}
