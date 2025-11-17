<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Extractors;

use App\Contracts\ElementExtractorInterface;
use App\DTOs\DocumentElement;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;

/**
 * Extracteur d'images depuis les documents
 */
final class ImageExtractor implements ElementExtractorInterface
{
    private const string STORAGE_PATH = 'documents/images';

    public function extract(string $rawText, string $filePath, int &$position): array
    {
        $images = [];

        // Extraire les références d'images dans le texte
        $imageReferences = $this->extractImageReferences($rawText);

        // Extraire les images réelles du fichier PDF
        $extractedImages = $this->extractImagesFromFile($filePath);

        // Associer les images extraites avec leurs références dans le texte
        foreach ($extractedImages as $index => $imageData) {
            $reference = $imageReferences[$index] ?? null;

            $element = new DocumentElement(
                type: 'image',
                content: $imageData['path'],
                position: $reference['position'] ?? $position++,
                attributes: [
                    'alt' => $imageData['alt'] ?? "Image {$index}",
                    'caption' => $imageData['caption'] ?? null,
                    'page' => $imageData['page'] ?? null,
                    'width' => $imageData['width'] ?? null,
                    'height' => $imageData['height'] ?? null,
                ],
            );

            $images[] = $element;
        }

        return $images;
    }

    /**
     * Extrait les références d'images dans le texte
     */
    private function extractImageReferences(string $text): array
    {
        $references = [];
        $patterns = [
            '/(Figure|Image|Fig\.|Img\.)\s*(\d+)/i',
            '/\[image:([^\]]+)\]/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $references[] = [
                        'reference' => $match[0],
                        'position' => $match[1],
                    ];
                }
            }
        }

        return $references;
    }

    /**
     * Extrait les images du fichier PDF ou Word
     */
    private function extractImagesFromFile(string $filePath): array
    {
        $images = [];

        if (!class_exists(Imagick::class)) {
            Log::warning('Imagick not installed, skipping image extraction');

            return $images;
        }

        try {
            $imagick = new Imagick;
            $imagick->readImage($filePath);

            foreach ($imagick as $pageNum => $page) {
                // Extraire toutes les images de la page
                $pageImages = $this->extractImagesFromPage($page, $pageNum + 1);
                $images = array_merge($images, $pageImages);
            }

            $imagick->clear();
            $imagick->destroy();

        } catch (Exception $e) {
            Log::error('Error extracting images: ' . $e->getMessage());
        }

        return $images;
    }

    /**
     * Extrait les images d'une page spécifique
     */
    private function extractImagesFromPage(Imagick $page, int $pageNumber): array
    {
        $images = [];

        try {
            $page->setIteratorIndex(0);
            $numImages = $page->getNumberImages();

            for ($i = 0; $i < $numImages; $i++) {
                $page->setIteratorIndex($i);
                $imageBlob = $page->getImageBlob();

                if ($imageBlob !== false && mb_strlen($imageBlob) > 1000) { // Ignore tiny images
                    $imagePath = $this->saveImage($imageBlob, $pageNumber, $i + 1);

                    if ($imagePath) {
                        $images[] = [
                            'path' => $imagePath,
                            'page' => $pageNumber,
                            'alt' => "Image {$i} de la page {$pageNumber}",
                            'caption' => null,
                            'width' => $page->getImageWidth(),
                            'height' => $page->getImageHeight(),
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning("Error extracting images from page {$pageNumber}: " . $e->getMessage());
        }

        return $images;
    }

    /**
     * Sauvegarde l'image et retourne le chemin
     */
    private function saveImage(string $imageBlob, int $pageNumber, int $imageNumber): ?string
    {
        try {
            $filename = sprintf('page_%d_image_%d_%s.png', $pageNumber, $imageNumber, uniqid());
            $path = self::STORAGE_PATH . '/' . $filename;

            Storage::put($path, $imageBlob);

            return $path;
        } catch (Exception $e) {
            Log::error('Error saving image: ' . $e->getMessage());

            return null;
        }
    }

    public function getElementType(): string
    {
        return 'image';
    }
}
