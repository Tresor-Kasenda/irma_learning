<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;

/**
 * Processeur pour garantir que chaque élément Markdown est sur une ligne séparée
 *
 * Ce processor s'assure que:
 * - Chaque titre (#, ##, ###) est sur sa propre ligne
 * - Chaque élément de liste (-, *, 1.) est sur sa propre ligne
 * - Les paragraphes sont séparés par des lignes vides
 * - Les tableaux, code blocks, etc. sont correctement espacés
 */
final class MarkdownLineBreakProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        $markdown = $content->markdown;

        // Assurer que chaque élément Markdown est sur sa propre ligne
        $markdown = $this->ensureProperLineBreaks($markdown);

        $content->markdown = $markdown;

        return $content;
    }

    public function getPriority(): int
    {
        return 55; // Entre MarkdownProcessor (50) et ContentStructureProcessor (60)
    }

    /**
     * Assure que chaque élément Markdown est correctement séparé sur sa propre ligne
     */
    private function ensureProperLineBreaks(string $markdown): string
    {
        // 1. S'assurer qu'il y a des sauts de ligne avant les titres
        $markdown = preg_replace('/([^\n])(#{1,6}\s+)/', "$1\n\n$2", $markdown);

        // 2. S'assurer qu'il y a des sauts de ligne après les titres
        $markdown = preg_replace('/(#{1,6}\s+.+)([^\n])/', "$1\n\n$2", $markdown);

        // 3. S'assurer qu'il y a des sauts de ligne avant les listes
        $markdown = preg_replace('/([^\n])(\n[-\*\+]\s+)/', "$1\n$2", $markdown);

        // 4. S'assurer qu'il y a des sauts de ligne avant les listes numérotées
        $markdown = preg_replace('/([^\n])(\n\d+\.\s+)/', "$1\n$2", $markdown);

        // 5. S'assurer qu'il y a des sauts de ligne avant les tableaux
        $markdown = preg_replace('/([^\n])(\n\|)/', "$1\n$2", $markdown);

        // 6. S'assurer qu'il y a des sauts de ligne avant les blocs de code
        $markdown = preg_replace('/([^\n])(\n```)/', "$1\n$2", $markdown);

        // 7. S'assurer qu'il y a des sauts de ligne après les blocs de code
        $markdown = preg_replace('/(```\n)([^\n])/', "$1\n$2", $markdown);

        // 8. S'assurer qu'il y a des sauts de ligne avant les citations
        $markdown = preg_replace('/([^\n])(\n>\s+)/', "$1\n$2", $markdown);

        // 9. Normaliser les multiples sauts de ligne (max 2 consécutifs)
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);

        // 10. Assurer un saut de ligne après les listes avant un paragraphe
        $markdown = preg_replace('/([-\*\+]\s+.+)\n([^-\*\+\n#\|>])/', "$1\n\n$2", $markdown);

        // 11. Assurer un saut de ligne après les listes numérotées avant un paragraphe
        $markdown = preg_replace('/(\d+\.\s+.+)\n([^\d\n#\|>-])/', "$1\n\n$2", $markdown);

        return mb_trim($markdown);
    }
}
