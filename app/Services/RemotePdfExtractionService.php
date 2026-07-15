<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class RemotePdfExtractionService
{
    /**
     * @return array{markdown: string, document_type: string, extraction_strategy: string, page_count: int, word_count: int, image_count: int, cover_file: string, visual_pages: array<int, int>, ocr_pages: array<int, int>, ocr_required_pages: array<int, int>, warnings: array<int, string>, asset_directory: string}
     */
    public function extract(string $pdfPath, string $assetDirectory): array
    {
        if (! is_file($pdfPath)) {
            throw new RuntimeException("Le PDF est introuvable : {$pdfPath}");
        }

        $apiUrl = config('learning.pdf_extraction.api_url');
        $timeout = (int) config('learning.pdf_extraction.timeout', 600);

        if (! $apiUrl) {
            throw new RuntimeException('API URL pour l\'extraction PDF non configurée');
        }

        try {
            $response = Http::timeout($timeout)
                ->attach('file', fopen($pdfPath, 'r'), basename($pdfPath))
                ->post("{$apiUrl}/extract", [
                    'max_pages' => (int) config('learning.pdf_extraction.max_pages', 0),
                    'batch_size' => (int) config('learning.pdf_extraction.batch_size', 50),
                    'parallel' => (int) config('learning.pdf_extraction.parallel', 0),
                    'image_dpi' => (int) config('learning.pdf_extraction.image_dpi', 144),
                    'visual_dpi' => (int) config('learning.pdf_extraction.visual_dpi', 110),
                    'ocr_language' => config('learning.pdf_extraction.ocr_language', 'fra+eng'),
                ]);

            if (! $response->successful()) {
                throw new RuntimeException("API Error: {$response->status()} - {$response->body()}");
            }

            $result = $response->json();

            if ($result['status'] === 'failed') {
                throw new RuntimeException($result['error'] ?? 'Extraction failed');
            }

            return [
                'markdown' => (string) $result['markdown'],
                'document_type' => (string) ($result['document_type'] ?? 'unknown'),
                'extraction_strategy' => (string) ($result['extraction_strategy'] ?? 'unknown'),
                'page_count' => (int) ($result['page_count'] ?? 0),
                'word_count' => (int) ($result['word_count'] ?? 0),
                'image_count' => (int) ($result['image_count'] ?? 0),
                'cover_file' => (string) ($result['cover_file'] ?? 'cover.png'),
                'visual_pages' => array_values(array_map('intval', $result['visual_pages'] ?? [])),
                'ocr_pages' => array_values(array_map('intval', $result['ocr_pages'] ?? [])),
                'ocr_required_pages' => array_values(array_map('intval', $result['ocr_required_pages'] ?? [])),
                'warnings' => array_values(array_map('strval', $result['warnings'] ?? [])),
                'asset_directory' => $assetDirectory,
            ];
        } catch (\Exception $e) {
            throw new RuntimeException("PDF extraction failed: {$e->getMessage()}");
        }
    }
}
