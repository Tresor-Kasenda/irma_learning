<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use Illuminate\Support\Facades\Log;

/**
 * Processeur pour extraire les images depuis des PDFs
 *
 * Note: L'extraction d'images embarquées dans les PDFs nécessite des outils
 * spécialisés comme pdfimages (poppler-utils) ou l'API native de smalot/pdfparser
 * qui n'est pas encore complètement supportée.
 *
 * Pour l'instant, ce processor est désactivé et retourne le contenu inchangé.
 * Une future version pourrait implémenter l'extraction via:
 * - poppler-utils (pdfimages command line)
 * - Imagick extension PHP
 * - API native de smalot/pdfparser pour accéder aux objets images
 */
final class PdfImageProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        $filePath = $content->options['filePath'] ?? null;

        if (! $filePath || ! file_exists($filePath)) {
            return $content;
        }

        try {
            // Vérifier si la classe Spatie\PdfToImage\Pdf existe
            if (! class_exists(\Spatie\PdfToImage\Pdf::class)) {
                Log::warning('Spatie\PdfToImage\Pdf class not found. Skipping image extraction.');
                return $content;
            }

            $pdf = new \Spatie\PdfToImage\Pdf($filePath);
            $totalPages = $pdf->pageCount();
            $textLength = mb_strlen($content->rawText);

            // Créer le dossier de destination s'il n'existe pas
            $outputDir = storage_path('app/public/extracts/images');
            if (! file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
                // Ignorer la première page si demandé (souvent la couverture)
                if (($content->options['skipFirstPage'] ?? false) && $pageNumber === 1) {
                    continue;
                }

                $imageName = uniqid() . '_page_' . $pageNumber . '.jpg';
                $fullPath = $outputDir . '/' . $imageName;
                $publicPath = 'extracts/images/' . $imageName;

                // Extraire la page en image
                $pdf->selectPage($pageNumber);
                $pdf->save($fullPath);

                // Ajouter l'élément image au contenu
                // Note: Sans information de position précise dans le texte, 
                // on ajoute les images à la fin du document pour l'instant.
                // Une amélioration future serait de corréler avec le texte de la page.
                $content->addElement(new \App\DTOs\DocumentElement(
                    type: 'image',
                    content: $publicPath,
                    position: $textLength, // À la fin du document
                    attributes: [
                        'alt' => "Page {$pageNumber}",
                        'caption' => "Image extraite de la page {$pageNumber}",
                        'page' => $pageNumber,
                    ]
                ));
            }

            Log::info('Images extracted from PDF', [
                'file' => $filePath,
                'count' => $totalPages,
            ]);

        } catch (\Exception $e) {
            Log::error('Error extracting images from PDF', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $content;
    }

    public function getPriority(): int
    {
        return 20; // Exécuté avant MarkdownProcessor (50)
    }
}
