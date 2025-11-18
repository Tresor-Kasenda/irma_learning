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
        // Titres avec ## déjà présents
        '/^#{1,6}\s+(.+)$/' => 'markdown',
        // Titres numérotés (1. Titre, 1.1 Titre, etc.)
        '/^(\d+(?:\.\d+)*\.?)\s+([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ].+)$/' => 'numbered',
        // Titres en MAJUSCULES (minimum 10 caractères pour éviter faux positifs)
        '/^([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ\s\d\-\'\",]{10,})$/' => 'uppercase',
        // Chapitres/Parties (Chapitre 1, Part II, etc.)
        '/^(chapitre|chapter|partie|part|section)\s+(\d+|[ivxlcdm]+)[\s\:\-]*(.*)$/i' => 'chapter',
    ];

    private const array LIST_PATTERNS = [
        // Sous-sections avec lettres majuscules après un titre (A), B), C))
        '/^([A-Z])\)\s+([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ].+)$/' => 'subsection',
        // Sous-sections avec lettres minuscules après un titre (a), b), c))
        '/^([a-z])\)\s+([A-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ].+)$/' => 'subsection',
        // Listes à puces (tous les caractères Unicode de puces)
        '/^[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–—o]\s+(.+)$/u' => 'bullet',
        // Listes numérotées avec point
        '/^(\d+)\.\s+(.+)$/' => 'numbered',
        // Listes avec parenthèses (chiffres uniquement)
        '/^(\d+)\)\s+(.+)$/' => 'parenthesis',
        // Listes avec lettres minuscules (pour les sous-listes)
        '/^([a-z])\)\s+(.+)$/' => 'parenthesis',
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
            'markdown' => mb_substr_count($matches[0], '#'), // Compter les #
            'chapter' => 1, // Les chapitres sont toujours H1
            'numbered' => $this->getTitleLevelFromNumbering($matches[1]),
            'uppercase' => 2, // Les titres en MAJUSCULES sont H2
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

        $text = match ($type) {
            'markdown' => mb_trim($matches[1] ?? $line), // Texte après les #
            'numbered' => mb_trim($matches[2] ?? $line), // Texte après le numéro
            'chapter' => mb_trim(($matches[3] ?? '') !== '' ? $matches[3] : ($matches[1].' '.$matches[2])), // Chapitre + numéro ou texte
            'uppercase' => mb_convert_case(mb_strtolower($line), MB_CASE_TITLE), // Convertir en Title Case
            default => mb_trim($line),
        };

        // Nettoyer les espaces multiples
        return preg_replace('/\s+/', ' ', $text);
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

            // Ne PAS fusionner les lignes - garder chaque ligne séparée pour préserver la structure
            // Si le paragraphe en cours n'est pas vide, le vider d'abord
            if (! empty($currentParagraph)) {
                $improvedLines[] = implode(' ', $currentParagraph);
                $currentParagraph = [];
            }

            // Ajouter la ligne directement
            $improvedLines[] = $line;
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
        return (bool) preg_match('/^(#{1,6}\s|[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–—]\s|\d+\.\s|[a-z]\)\s|\|.*\||```|>|\$\$)/iu', $line);
    }

    /**
     * Structure les listes
     */
    private function structureLists(string $markdown): string
    {
        $lines = explode("\n", $markdown);
        $structuredLines = [];
        $inList = false;
        $previousWasTitle = false;

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            if (empty($trimmedLine)) {
                // Fin de liste
                if ($inList) {
                    $structuredLines[] = '';
                    $inList = false;
                }
                $structuredLines[] = $line;
                $previousWasTitle = false;

                continue;
            }

            // Vérifier si c'est un titre
            $isTitle = preg_match('/^#{1,6}\s+/', $trimmedLine);

            // Détecter les listes
            $listInfo = $this->detectList($trimmedLine);

            if ($listInfo) {
                // Si c'est une subsection (a), b), c)) juste après un titre, la convertir en H3
                if ($listInfo['type'] === 'subsection' && $previousWasTitle) {
                    $structuredLines[] = '';
                    $structuredLines[] = $this->formatListItem($trimmedLine, $listInfo);
                    $structuredLines[] = '';
                    $previousWasTitle = false;
                    $inList = false;
                } else {
                    // Début de liste normale
                    if (! $inList) {
                        if (! empty($structuredLines) && mb_trim(end($structuredLines)) !== '') {
                            $structuredLines[] = '';
                        }
                        $inList = true;
                    }

                    $structuredLines[] = $this->formatListItem($trimmedLine, $listInfo);
                    $previousWasTitle = false;
                }
            } else {
                // Fin de liste
                if ($inList) {
                    $structuredLines[] = '';
                    $inList = false;
                }
                $structuredLines[] = $line;
                $previousWasTitle = $isTitle;
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
            'bullet' => '- '.mb_trim($matches[1] ?? ''),
            'numbered' => $matches[1].'. '.mb_trim($matches[2] ?? ''),
            'parenthesis' => '  - '.mb_trim($matches[2] ?? ''), // Sous-liste indentée
            'subsection' => '### '.mb_trim($matches[2] ?? ''), // Sous-section en H3
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
