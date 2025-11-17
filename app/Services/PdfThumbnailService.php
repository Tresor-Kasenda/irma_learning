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
     * @param  string  $pdfPath  Chemin absolu vers le fichier PDF
     * @param  array  $options  Options de configuration:
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
            // Vérifier que le fichier existe
            if (! file_exists($pdfPath)) {
                throw new Exception("Fichier PDF introuvable: {$pdfPath}");
            }

            // Vérifier que Imagick est installé
            if (! extension_loaded('imagick')) {
                Log::warning('Extension Imagick non disponible, impossible de générer la miniature');

                return null;
            }

            // Configuration
            $width = $options['width'] ?? self::DEFAULT_WIDTH;
            $height = $options['height'] ?? self::DEFAULT_HEIGHT;
            $format = $options['format'] ?? self::FORMAT;
            $quality = $options['quality'] ?? self::QUALITY;

            // Créer l'instance PDF
            $pdf = new Pdf($pdfPath);

            // Générer le nom de fichier unique
            $filename = $this->generateFilename($format);
            $tempPath = storage_path('app/temp/'.$filename);

            // S'assurer que le dossier temp existe
            $tempDir = dirname($tempPath);
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Configurer et générer la miniature de la première page
            $pdf->selectPage(1) // Première page
                ->resolution(150) // Bonne qualité
                ->quality($quality) // Qualité de compression
                ->format($format === 'png' ? OutputFormat::Png : OutputFormat::Jpg)
                ->save($tempPath);

            // Redimensionner si nécessaire
            if (file_exists($tempPath)) {
                $this->resizeImage($tempPath, $width, $height);

                // Déplacer vers le stockage permanent
                $storagePath = self::STORAGE_PATH.'/'.$filename;
                Storage::disk('public')->put($storagePath, file_get_contents($tempPath));

                // Nettoyer le fichier temporaire
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
     * Supprime une miniature du stockage
     */
    public function deleteThumbnail(?string $thumbnailPath): bool
    {
        if (! $thumbnailPath) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($thumbnailPath)) {
                return Storage::disk('public')->delete($thumbnailPath);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression de la miniature', [
                'path' => $thumbnailPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Vérifie si une miniature existe
     */
    public function thumbnailExists(?string $thumbnailPath): bool
    {
        if (! $thumbnailPath) {
            return false;
        }

        return Storage::disk('public')->exists($thumbnailPath);
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
        if (! extension_loaded('imagick')) {
            return;
        }

        try {
            $imagick = new Imagick($imagePath);

            // Obtenir les dimensions actuelles
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            // Calculer les nouvelles dimensions en conservant le ratio
            $ratio = min($maxWidth / $width, $maxHeight / $height);

            if ($ratio < 1) {
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);

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
