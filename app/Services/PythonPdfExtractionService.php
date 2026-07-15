<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use JsonException;
use Log;
use RuntimeException;
use Symfony\Component\Process\Process;

final class PythonPdfExtractionService
{
    /**
     * @return array{markdown: string, document_type: string, extraction_strategy: string, page_count: int, word_count: int, image_count: int, cover_file: string, visual_pages: array<int, int>, ocr_pages: array<int, int>, ocr_required_pages: array<int, int>, warnings: array<int, string>, asset_directory: string}
     */
    public function extract(string $pdfPath, string $assetDirectory): array
    {
        $pythonBinary = mb_trim((string) config('learning.pdf_extraction.python_binary'));
        $pythonBinary = $pythonBinary !== '' ? $pythonBinary : 'python3';
        $scriptPath = (string) config('learning.pdf_extraction.script_path');

        if (! is_file($pdfPath)) {
            throw new RuntimeException("Le PDF est introuvable : {$pdfPath}");
        }
        if (! is_file($scriptPath)) {
            throw new RuntimeException("Le script Python est introuvable : {$scriptPath}");
        }

        $outputPath = Storage::disk('public')->path($assetDirectory);
        if ($outputPath === null || $outputPath === '') {
            throw new RuntimeException("Le répertoire de sortie n'a pas pu être déterminé : {$assetDirectory}");
        }

        Storage::disk('public')->deleteDirectory($assetDirectory);
        Storage::disk('public')->makeDirectory($assetDirectory);

        $maxPages = (int) config('learning.pdf_extraction.max_pages', 0);
        $batchSize = (int) config('learning.pdf_extraction.batch_size', 50);
        $parallel = (int) config('learning.pdf_extraction.parallel', 0);
        $imageDpi = (int) config('learning.pdf_extraction.image_dpi', 144);
        $visualDpi = (int) config('learning.pdf_extraction.visual_dpi', 110);
        $ocrLanguage = (string) config('learning.pdf_extraction.ocr_language', 'fra+eng');

        $command = [
            $pythonBinary,
            $scriptPath,
            '--input',
            $pdfPath,
            '--output-dir',
            $outputPath,
            '--public-prefix',
            '/storage/'.mb_ltrim($assetDirectory, '/'),
            '--max-pages',
            (string) $maxPages,
            '--image-dpi',
            (string) $imageDpi,
            '--visual-dpi',
            (string) $visualDpi,
            '--ocr-language',
            $ocrLanguage,
            '--batch-size',
            (string) $batchSize,
            '--parallel',
            (string) $parallel,
        ];

        Log::debug('PDF extraction command', [
            'python' => $pythonBinary,
            'script' => $scriptPath,
            'input' => $pdfPath,
            'output' => $outputPath,
        ]);

        $process = new Process($command, base_path());
        $process->setTimeout((float) config('learning.pdf_extraction.timeout', 600));
        $process->run();

        if (! $process->isSuccessful()) {
            $message = $this->extractErrorMessage($process->getErrorOutput());
            Storage::disk('public')->deleteDirectory($assetDirectory);

            throw new RuntimeException($message);
        }

        try {
            $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Storage::disk('public')->deleteDirectory($assetDirectory);

            throw new RuntimeException('La réponse du convertisseur Python est invalide.', previous: $exception);
        }

        if (! is_array($result) || ! isset($result['markdown'], $result['cover_file'])) {
            Storage::disk('public')->deleteDirectory($assetDirectory);

            throw new RuntimeException('Le convertisseur Python n’a pas retourné le contenu attendu.');
        }

        return [
            'markdown' => (string) $result['markdown'],
            'document_type' => (string) ($result['document_type'] ?? 'unknown'),
            'extraction_strategy' => (string) ($result['extraction_strategy'] ?? 'unknown'),
            'page_count' => (int) ($result['page_count'] ?? 0),
            'word_count' => (int) ($result['word_count'] ?? 0),
            'image_count' => (int) ($result['image_count'] ?? 0),
            'cover_file' => (string) $result['cover_file'],
            'visual_pages' => array_values(array_map('intval', $result['visual_pages'] ?? [])),
            'ocr_pages' => array_values(array_map('intval', $result['ocr_pages'] ?? [])),
            'ocr_required_pages' => array_values(array_map('intval', $result['ocr_required_pages'] ?? [])),
            'warnings' => array_values(array_map('strval', $result['warnings'] ?? [])),
            'asset_directory' => $assetDirectory,
        ];
    }

    private function extractErrorMessage(string $errorOutput): string
    {
        $errorOutput = mb_trim($errorOutput);
        if ($errorOutput === '') {
            return 'La conversion Python du PDF a échoué.';
        }

        try {
            $error = json_decode($errorOutput, true, flags: JSON_THROW_ON_ERROR);

            return is_array($error) && is_string($error['error'] ?? null)
                ? $error['error']
                : $errorOutput;
        } catch (JsonException) {
            return $errorOutput;
        }
    }
}
