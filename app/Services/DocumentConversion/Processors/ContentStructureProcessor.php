<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;

/**
 * Processeur pour améliorer la structure et la lisibilité du contenu Markdown
 */
final class ContentStructureProcessor implements ContentProcessorInterface
{
    /**
     * Patterns pour détecter les différents types de contenu
     */
    private const array TITLE_PATTERNS = [
        // Titres numérotés (1.2.3 Titre)
        '/^(\d+(?:\.\d+)*)\s+(.+)$/' => 'numbered',
        // Titres en majuscules
        '/^([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ\s\d\-\.\'\"]{3,})$/' => 'uppercase',
        // Chapitres (Chapitre 1, Chapter 1, etc.)
        '/^(chapitre|chapter|partie|part)\s+(\d+|[ivxlcdm]+)[\s\:]+(.+)/i' => 'chapter',
    ];

    private const array LIST_PATTERNS = [
        // Listes à puces
        '/^[•\-\*\+]\s+(.+)$/' => 'bullet',
        // Listes numérotées
        '/^(\d+|[a-z])\.\s+(.+)$/' => 'numbered',
        // Listes avec parenthèses
        '/^(\d+|[a-z])\)\s+(.+)$/' => 'parenthesis',
    ];

    public function process(DocumentContent $content): DocumentContent
    {
        $markdown = $content->markdown;

        // 1. Normaliser les sauts de ligne
        $markdown = $this->normalizeLineBreaks($markdown);

        // 2. Structurer les titres hiérarchiquement
        $markdown = $this->structureTitles($markdown);

        // 3. Améliorer les paragraphes
        $markdown = $this->improveParagraphs($markdown);

        // 4. Structurer les listes
        $markdown = $this->structureLists($markdown);

        // 5. Ajouter des séparateurs visuels
        $markdown = $this->addVisualSeparators($markdown);

        // 6. Nettoyer les espaces multiples
        $markdown = $this->cleanupSpacing($markdown);

        $content->markdown = $markdown;

        return $content;
    }

    public function getPriority(): int
    {
        return 60; // Exécuté après MarkdownProcessor (50)
    }

    /**
     * Normalise les sauts de ligne
     */
    private function normalizeLineBreaks(string $markdown): string
    {
        // Remplacer les multiples sauts de ligne par maximum 2
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);

        return $markdown;
    }

    /**
     * Structure les titres de manière hiérarchique
     */
    private function structureTitles(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $structuredLines = [];
        $titleHierarchy = [];

        foreach ($lines as $index => $line) {
            $trimmedLine = mb_trim($line);

            // Ignorer les lignes vides
            if (empty($trimmedLine)) {
                $structuredLines[] = $line;

                continue;
            }

            // Vérifier si c'est déjà un titre Markdown
            if (preg_match('/^#{1,6}\s+/', $trimmedLine)) {
                $structuredLines[] = $this->normalizeTitle($line);

                continue;
            }

            // Détecter les titres avec patterns
            $titleInfo = $this->detectTitle($trimmedLine);

            if ($titleInfo) {
                $level = $this->determineTitleLevel($titleInfo, $titleHierarchy);
                $titleText = $this->extractTitleText($trimmedLine, $titleInfo);

                // Ajouter un saut de ligne avant les titres (sauf premier)
                if (! empty($structuredLines) && mb_trim(end($structuredLines)) !== '') {
                    $structuredLines[] = '';
                }

                $structuredLines[] = str_repeat('#', $level).' '.$titleText;

                // Ajouter un saut de ligne après
                $structuredLines[] = '';

                // Mettre à jour la hiérarchie
                $titleHierarchy[$level] = $titleText;
            } else {
                $structuredLines[] = $line;
            }
        }

        return implode("\n", $structuredLines);
    }

    /**
     * Détecte si une ligne est un titre
     */
    private function detectTitle(string $line): ?array
    {
        foreach (self::TITLE_PATTERNS as $pattern => $type) {
            if (preg_match($pattern, $line, $matches)) {
                return [
                    'type' => $type,
                    'matches' => $matches,
                    'line' => $line,
                ];
            }
        }

        return null;
    }

    /**
     * Détermine le niveau du titre
     */
    private function determineTitleLevel(array $titleInfo, array $hierarchy): int
    {
        $type = $titleInfo['type'];
        $matches = $titleInfo['matches'];

        return match ($type) {
            'chapter' => 1,
            'numbered' => $this->getTitleLevelFromNumbering($matches[1]),
            'uppercase' => $this->getTitleLevelFromLength($titleInfo['line']),
            default => 2,
        };
    }

    /**
     * Obtient le niveau de titre depuis la numérotation
     */
    private function getTitleLevelFromNumbering(string $numbering): int
    {
        // Compter les points pour déterminer le niveau
        $level = mb_substr_count($numbering, '.') + 1;

        return min($level, 6); // Maximum H6
    }

    /**
     * Obtient le niveau de titre depuis la longueur
     */
    private function getTitleLevelFromLength(string $line): int
    {
        $length = mb_strlen($line);

        if ($length < 20) {
            return 1; // Court = H1
        }
        if ($length < 40) {
            return 2; // Moyen = H2
        }

        return 3; // Long = H3

    }

    /**
     * Extrait le texte du titre
     */
    private function extractTitleText(string $line, array $titleInfo): string
    {
        $type = $titleInfo['type'];
        $matches = $titleInfo['matches'];

        return match ($type) {
            'numbered' => mb_trim($matches[2] ?? $line),
            'chapter' => mb_trim($matches[3] ?? $line),
            'uppercase' => mb_convert_case(mb_strtolower($line), MB_CASE_TITLE),
            default => mb_trim($line),
        };
    }

    /**
     * Normalise un titre déjà formaté
     */
    private function normalizeTitle(string $line): string
    {
        // Assurer un espace après les #
        return preg_replace('/^(#{1,6})([^\s])/', '$1 $2', $line);
    }

    /**
     * Améliore la structure des paragraphes
     */
    private function improveParagraphs(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $improvedLines = [];
        $inCodeBlock = false;
        $currentParagraph = [];

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            // Gérer les blocs de code
            if (preg_match('/^```/', $trimmedLine)) {
                // Vider le paragraphe en cours
                if (! empty($currentParagraph)) {
                    $improvedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                    $improvedLines[] = '';
                }

                $inCodeBlock = ! $inCodeBlock;
                $improvedLines[] = $line;

                continue;
            }

            // Ne pas traiter les lignes dans les blocs de code
            if ($inCodeBlock) {
                $improvedLines[] = $line;

                continue;
            }

            // Ligne vide = fin de paragraphe
            if (empty($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $improvedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                }
                $improvedLines[] = '';

                continue;
            }

            // Ne pas fusionner les titres, listes, tableaux
            if ($this->isSpecialLine($trimmedLine)) {
                // Vider le paragraphe en cours
                if (! empty($currentParagraph)) {
                    $improvedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                    $improvedLines[] = '';
                }

                $improvedLines[] = $line;

                continue;
            }

            // Accumuler les lignes du paragraphe
            $currentParagraph[] = $trimmedLine;
        }

        // Vider le dernier paragraphe
        if (! empty($currentParagraph)) {
            $improvedLines[] = implode(' ', $currentParagraph);
        }

        return implode("\n", $improvedLines);
    }

    /**
     * Vérifie si une ligne est spéciale (titre, liste, tableau, etc.)
     */
    private function isSpecialLine(string $line): bool
    {
        return (bool) preg_match('/^(#{1,6}\s|[-*+]\s|\d+\.\s|\|.*\||```|>|\$\$)/', $line);
    }

    /**
     * Structure les listes
     */
    private function structureLists(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $structuredLines = [];
        $inList = false;

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            if (empty($trimmedLine)) {
                // Fin de liste
                if ($inList) {
                    $structuredLines[] = '';
                    $inList = false;
                }
                $structuredLines[] = $line;

                continue;
            }

            // Détecter les listes
            $listInfo = $this->detectList($trimmedLine);

            if ($listInfo) {
                // Début de liste
                if (! $inList) {
                    if (! empty($structuredLines) && mb_trim(end($structuredLines)) !== '') {
                        $structuredLines[] = '';
                    }
                    $inList = true;
                }

                $structuredLines[] = $this->formatListItem($trimmedLine, $listInfo);
            } else {
                // Fin de liste
                if ($inList) {
                    $structuredLines[] = '';
                    $inList = false;
                }
                $structuredLines[] = $line;
            }
        }

        return implode("\n", $structuredLines);
    }

    /**
     * Détecte si une ligne est un élément de liste
     */
    private function detectList(string $line): ?array
    {
        foreach (self::LIST_PATTERNS as $pattern => $type) {
            if (preg_match($pattern, $line, $matches)) {
                return [
                    'type' => $type,
                    'matches' => $matches,
                ];
            }
        }

        return null;
    }

    /**
     * Formate un élément de liste
     */
    private function formatListItem(string $line, array $listInfo): string
    {
        $type = $listInfo['type'];
        $matches = $listInfo['matches'];

        return match ($type) {
            'bullet' => '- '.mb_trim($matches[1]),
            'numbered' => $matches[1].'. '.mb_trim($matches[2]),
            'parenthesis' => $matches[1].') '.mb_trim($matches[2]),
            default => $line,
        };
    }

    /**
     * Ajoute des séparateurs visuels entre les sections
     */
    private function addVisualSeparators(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $result = [];
        $previousWasH1 = false;

        foreach ($lines as $index => $line) {
            $trimmedLine = mb_trim($line);

            // Ajouter séparateur avant H1 (sauf premier)
            if (preg_match('/^#\s+/', $trimmedLine)) {
                if ($previousWasH1 && $index > 0) {
                    $result[] = '';
                    $result[] = '---';
                    $result[] = '';
                }
                $previousWasH1 = true;
            } else {
                $previousWasH1 = false;
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    /**
     * Nettoie les espacements multiples
     */
    private function cleanupSpacing(string $markdown): string
    {
        // Remplacer 3+ lignes vides par 2
        $markdown = preg_replace('/\n{4,}/', "\n\n\n", $markdown);

        // Supprimer les espaces en fin de ligne
        $markdown = preg_replace('/[ \t]+$/m', '', $markdown);

        // Supprimer les espaces multiples dans les lignes
        $markdown = preg_replace('/ {2,}/', ' ', $markdown);

        return mb_trim($markdown);
    }
}
