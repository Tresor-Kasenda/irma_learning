<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;

/**
 * Processeur d'amélioration de la qualité du Markdown
 * Utilise une approche sémantique pour restructurer et nettoyer le Markdown
 */
final class MarkdownEnhancementProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        $markdown = $content->markdown;

        // 1. Nettoyer tous les caractères Unicode problématiques
        $markdown = $this->cleanUnicodeCharacters($markdown);

        // 2. Reconstruire complètement la structure
        $markdown = $this->rebuildStructure($markdown);

        // 3. Améliorer les listes et sous-listes
        $markdown = $this->enhanceLists($markdown);

        // 4. Ajouter des espaces blancs pour la lisibilité
        $markdown = $this->improveReadability($markdown);

        // 5. Nettoyer les lignes vides excessives
        $markdown = $this->cleanupWhitespace($markdown);

        $content->markdown = $markdown;

        return $content;
    }

    /**
     * Nettoie tous les caractères Unicode problématiques
     */
    private function cleanUnicodeCharacters(string $markdown): string
    {
        $markdown = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $markdown);

        $markdown = preg_replace('/[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡]/u', '-', $markdown);

        return preg_replace('/\h+/', ' ', $markdown);
    }

    /**
     * Reconstruit complètement la structure du document
     */
    private function rebuildStructure(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $rebuilt = [];
        $inCodeBlock = false;

        foreach ($lines as $line) {
            $trimmed = mb_trim($line);

            if (preg_match('/^```/', $trimmed)) {
                $inCodeBlock = !$inCodeBlock;
                $rebuilt[] = $line;

                continue;
            }

            if ($inCodeBlock) {
                $rebuilt[] = $line;

                continue;
            }

            if (empty($trimmed)) {
                $rebuilt[] = '';

                continue;
            }

            if ($this->isHeading($trimmed)) {
                if (!empty($rebuilt) && mb_trim(end($rebuilt)) !== '') {
                    $rebuilt[] = '';
                }
                $rebuilt[] = $this->normalizeHeading($trimmed);
                $rebuilt[] = '';
            } elseif ($this->isListItem($trimmed)) {
                $rebuilt[] = $this->normalizeListItem($trimmed);
            } elseif ($this->isCheckboxItem($trimmed)) {
                $rebuilt[] = $this->normalizeCheckbox($trimmed);
            } else {
                $rebuilt[] = $trimmed;
            }
        }

        return implode("\n", $rebuilt);
    }

    /**
     * Vérifie si une ligne est un titre
     */
    private function isHeading(string $line): bool
    {
        return (bool)preg_match('/^#{1,6}\s+/', $line);
    }

    /**
     * Normalise un titre
     */
    private function normalizeHeading(string $line): string
    {
        if (preg_match('/^(#{1,6})([^\s])/', $line, $matches)) {
            return $matches[1] . ' ' . mb_substr($line, mb_strlen($matches[1]));
        }

        return $line;
    }

    /**
     * Vérifie si une ligne est un élément de liste
     */
    private function isListItem(string $line): bool
    {
        return (bool)preg_match('/^[\-\*\+]\s+/', $line) ||
            (bool)preg_match('/^\d+\.\s+/', $line);
    }

    /**
     * Normalise un élément de liste
     */
    private function normalizeListItem(string $line): string
    {
        $level = 0;
        $cleanLine = $line;

        if (preg_match('/^(\s*)(.+)$/', $line, $matches)) {
            $indent = $matches[1];
            $level = (int)(mb_strlen($indent) / 2); // 2 espaces = 1 niveau
            $cleanLine = $matches[2];
        }

        $cleanLine = preg_replace('/^[\-\*\+]\s*/', '', $cleanLine);
        $cleanLine = preg_replace('/^\d+\.\s*/', '', $cleanLine);
        $cleanLine = mb_trim($cleanLine);

        $indent = str_repeat('  ', $level);

        return $indent . '- ' . $cleanLine;
    }

    /**
     * Vérifie si c'est une checkbox
     */
    private function isCheckboxItem(string $line): bool
    {
        return (bool)preg_match('/^\-\s*\[\s*[x\s]\s*\]/', $line);
    }

    /**
     * Normalise une checkbox
     */
    private function normalizeCheckbox(string $line): string
    {
        $checked = (bool)preg_match('/^\-\s*\[\s*x\s*\]/i', $line);
        $text = preg_replace('/^\-\s*\[\s*[x\s]\s*\]\s*/', '', $line);
        $text = mb_trim($text);

        return '- [' . ($checked ? 'x' : ' ') . '] ' . $text;
    }

    /**
     * Améliore les listes et sous-listes
     */
    private function enhanceLists(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $enhanced = [];
        $inList = false;
        $lastWasList = false;

        foreach ($lines as $line) {
            $trimmed = mb_trim($line);

            if (empty($trimmed)) {
                if ($inList) {
                    $enhanced[] = '';
                    $inList = false;
                }
                $enhanced[] = '';
                $lastWasList = false;

                continue;
            }

            $isListItem = $this->isListItem($trimmed) || $this->isCheckboxItem($trimmed);

            if ($isListItem) {
                if (!$inList && $lastWasList) {
                    $enhanced[] = '';
                }
                $inList = true;
                $lastWasList = true;
                $enhanced[] = $line;
            } else {
                if ($inList) {
                    $enhanced[] = '';
                    $inList = false;
                }
                $lastWasList = false;
                $enhanced[] = $line;
            }
        }

        return implode("\n", $enhanced);
    }

    /**
     * Améliore la lisibilité en ajoutant des espaces stratégiques
     */
    private function improveReadability(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $improved = [];
        $previousType = null;

        foreach ($lines as $line) {
            $trimmed = mb_trim($line);

            if (empty($trimmed)) {
                $improved[] = '';
                $previousType = 'empty';

                continue;
            }

            $currentType = $this->getLineType($trimmed);

            if ($previousType && $previousType !== 'empty' && $currentType !== $previousType) {
                if (end($improved) !== '') {
                    $improved[] = '';
                }
            }

            $improved[] = $line;
            $previousType = $currentType;
        }

        return implode("\n", $improved);
    }

    /**
     * Détermine le type d'une ligne
     */
    private function getLineType(string $line): string
    {
        if ($this->isHeading($line)) {
            return 'heading';
        }
        if ($this->isListItem($line) || $this->isCheckboxItem($line)) {
            return 'list';
        }
        if (preg_match('/^```/', $line)) {
            return 'code';
        }
        if (preg_match('/^\|.*\|/', $line)) {
            return 'table';
        }

        return 'paragraph';
    }

    /**
     * Nettoie les espaces blancs excessifs
     */
    private function cleanupWhitespace(string $markdown): string
    {
        $markdown = preg_replace('/[ \t]+$/m', '', $markdown);
        $markdown = preg_replace('/\n{4,}/', "\n\n\n", $markdown);

        return mb_rtrim($markdown) . "\n";
    }

    public function getPriority(): int
    {
        return 70; // Exécuté en dernier, après tous les autres processors
    }
}
