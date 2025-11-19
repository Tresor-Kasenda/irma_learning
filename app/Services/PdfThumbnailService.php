<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Spatie\PdfToImage\Enums\OutputFormat;
use Spatie\PdfToImage\Pdf;

/**
 * Service pour générer des miniatures à partir de PDFs
 */
final class PdfThumbnailService
{
    private const string STORAGE_PATH = 'chapters/thumbnails';

    private const int DEFAULT_WIDTH = 1200;

    private const int DEFAULT_HEIGHT = 1600;

    private const string FORMAT = 'jpg';

    private const int QUALITY = 90;

    /**
     * Génère une miniature de la première page du PDF
     *
     * @param string $pdfPath Chemin absolu vers le fichier PDF
     * @param array $options Options de configuration:
     *                          - width: int (largeur en pixels)
     *                          - height: int (hauteur en pixels)
     *                          - format: string (jpg, png)
     *                          - quality: int (1-100)
     * @return string|null Chemin de stockage de l'image générée
     *
     * @throws Exception
     */
    public function generateThumbnail(string $pdfPath, array $options = []): ?string
    {
        try {
            if (!file_exists($pdfPath)) {
                throw new Exception("Fichier PDF introuvable: {$pdfPath}");
            }

            if (!extension_loaded('imagick')) {
                Log::warning('Extension Imagick non disponible, impossible de générer la miniature');

                return null;
            }

            $width = $options['width'] ?? self::DEFAULT_WIDTH;
            $height = $options['height'] ?? self::DEFAULT_HEIGHT;
            $format = $options['format'] ?? self::FORMAT;
            $quality = $options['quality'] ?? self::QUALITY;

            $pdf = new Pdf($pdfPath);

            $filename = $this->generateFilename($format);
            $tempPath = storage_path('app/temp/' . $filename);

            $tempDir = dirname($tempPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $pdf->selectPage(1)
                ->resolution(150)
                ->quality($quality)
                ->format($format === 'png' ? OutputFormat::Png : OutputFormat::Jpg)
                ->save($tempPath);

            if (file_exists($tempPath)) {
                $this->resizeImage($tempPath, $width, $height);

                $storagePath = self::STORAGE_PATH . '/' . $filename;
                Storage::disk('public')->put($storagePath, file_get_contents($tempPath));

                @unlink($tempPath);

                Log::info('Miniature PDF générée avec succès', [
                    'pdf' => basename($pdfPath),
                    'thumbnail' => $storagePath,
                    'size' => "{$width}x{$height}",
                ]);

                return $storagePath;
            }

            throw new Exception('Échec de la génération de la miniature');
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération de la miniature PDF', [
                'pdf' => $pdfPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Génère un nom de fichier unique pour la miniature
     */
    private function generateFilename(string $format): string
    {
        return sprintf(
            'thumbnail_%s.%s',
            uniqid('', true),
            $format
        );
    }

    /**
     * Redimensionne l'image tout en conservant les proportions
     */
    private function resizeImage(string $imagePath, int $maxWidth, int $maxHeight): void
    {
        if (!extension_loaded('imagick')) {
            return;
        }

        try {
            $imagick = new Imagick($imagePath);

            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            $ratio = min($maxWidth / $width, $maxHeight / $height);

            if ($ratio < 1) {
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);

                $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
                $imagick->writeImage($imagePath);
            }

            $imagick->clear();
            $imagick->destroy();

        } catch (Exception $e) {
            Log::warning('Erreur lors du redimensionnement de l\'image', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
