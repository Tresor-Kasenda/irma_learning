<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use Illuminate\Support\Facades\Log;

/**
 * Processeur pour détecter et formater les formules mathématiques
 *
 * Note: La détection automatique de formules mathématiques est très complexe
 * et génère beaucoup de faux positifs (titres, sections, numérotations).
 *
 * Pour l'instant, ce processor est désactivé. Une future version pourrait:
 * - Utiliser des marqueurs explicites ($$formule$$, \[formule\])
 * - Détecter uniquement les symboles mathématiques Unicode spéciaux
 * - Analyser le contexte pour éviter les faux positifs
 */
final class PdfFormulaProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        // Désactivé temporairement - la détection automatique génère trop de faux positifs
        // Les formules devraient être marquées explicitement dans le PDF source

        Log::debug('PdfFormulaProcessor: Formula detection is currently disabled', [
            'file' => $content->options['filePath'] ?? 'unknown',
            'reason' => 'Too many false positives with automatic detection',
        ]);

        return $content;
    }

    public function getPriority(): int
    {
        return 40; // Exécuté avant MarkdownProcessor (50)
    }
}
