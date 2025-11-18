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
        // Désactivé temporairement - l'extraction d'images embarquées nécessite
        // des outils spécialisés. Pour l'instant, on se concentre sur
        // l'extraction de texte, tableaux et formules mathématiques.

        Log::debug('PdfImageProcessor: Image extraction is currently disabled', [
            'file' => $content->options['filePath'] ?? 'unknown',
            'reason' => 'Requires specialized tools like poppler-utils or Imagick',
        ]);

        return $content;
    }

    public function getPriority(): int
    {
        return 20; // Exécuté avant MarkdownProcessor (50)
    }
}
