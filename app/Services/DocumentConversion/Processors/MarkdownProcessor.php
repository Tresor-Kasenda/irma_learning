<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use Illuminate\Support\Facades\Storage;

/**
 * Processeur pour convertir le contenu en Markdown
 */
final class MarkdownProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        $markdown = $this->convertToMarkdown($content);

        $content->markdown = $markdown;

        return $content;
    }

    public function getPriority(): int
    {
        return 50; // Exécuté après l'extraction des éléments
    }

    /**
     * Convertit le contenu en Markdown
     */
    private function convertToMarkdown(DocumentContent $content): string
    {
        // Trier les éléments par position
        $content->sortElementsByPosition();

        $markdown = '';
        $rawText = $content->rawText;
        $elements = $content->elements;
        $lastPosition = 0;

        foreach ($elements as $element) {
            // Ajouter le texte brut entre les éléments
            if ($element->position > $lastPosition) {
                $textSegment = mb_substr($rawText, $lastPosition, $element->position - $lastPosition);
                $markdown .= $this->formatTextSegment($textSegment);
            }

            // Ajouter l'élément formaté
            $markdown .= $this->formatElement($element);

            // Mettre à jour la position
            $contentLength = is_string($element->content) ? mb_strlen($element->content) : 0;
            $lastPosition = $element->position + $contentLength;
        }

        // Ajouter le reste du texte
        if ($lastPosition < mb_strlen($rawText)) {
            $textSegment = mb_substr($rawText, $lastPosition);
            $markdown .= $this->formatTextSegment($textSegment);
        }

        return mb_trim($markdown);
    }

    /**
     * Formate un segment de texte brut
     */
    private function formatTextSegment(string $text): string
    {
        $lines = explode("\n", $text);
        $formattedLines = [];
        $currentParagraph = [];

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            // Ligne vide = nouveau paragraphe
            if (empty($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode("\n", $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = '';

                continue;
            }

            // Détecte les listes à puces
            if ($this->isBulletPoint($trimmedLine)) {
                // Vider le paragraphe en cours
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode("\n", $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = '- '.mb_ltrim($trimmedLine, '•-*• ');

                continue;
            }

            // Détecte les listes numérotées
            if ($this->isNumberedList($trimmedLine)) {
                // Vider le paragraphe en cours
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode("\n", $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = $trimmedLine;

                continue;
            }

            // Ligne normale = ajouter au paragraphe
            $currentParagraph[] = $trimmedLine;
        }

        // Vider le dernier paragraphe
        if (! empty($currentParagraph)) {
            $formattedLines[] = implode("\n", $currentParagraph);
        }

        return implode("\n", $formattedLines)."\n\n";
    }

    /**
     * Vérifie si une ligne est une liste à puces
     */
    private function isBulletPoint(string $line): int
    {
        return preg_match('/^[•\-\*\+−–—o]\s+/', $line);
    }

    /**
     * Vérifie si une ligne est une liste numérotée
     */
    private function isNumberedList(string $line): int
    {
        return preg_match('/^\d+\.\s+/', $line);
    }

    /**
     * Formate un élément selon son type
     */
    private function formatElement($element): string
    {
        return match ($element->type) {
            'image' => $this->formatImage($element),
            'table' => $this->formatTable($element),
            'formula' => $this->formatFormula($element),
            'code' => $this->formatCode($element),
            default => '',
        };
    }

    /**
     * Formate une image en Markdown
     */
    private function formatImage($element): string
    {
        $alt = $element->getAttribute('alt', 'Image');
        $caption = $element->getAttribute('caption');
        $path = Storage::url($element->content);

        $markdown = "![{$alt}]({$path})";

        if ($caption) {
            $markdown .= "\n*{$caption}*";
        }

        return $markdown."\n\n";
    }

    /**
     * Formate un tableau en Markdown
     */
    private function formatTable($element): string
    {
        $headers = $element->content['headers'] ?? [];
        $rows = $element->content['rows'] ?? [];

        if (empty($headers)) {
            return '';
        }

        $markdown = [];

        // En-têtes
        $markdown[] = '| '.implode(' | ', $headers).' |';

        // Séparateur
        $markdown[] = '| '.implode(' | ', array_fill(0, count($headers), '---')).' |';

        // Lignes de données
        foreach ($rows as $row) {
            // Assurer le même nombre de colonnes
            $row = array_pad($row, count($headers), '');
            $markdown[] = '| '.implode(' | ', $row).' |';
        }

        return implode("\n", $markdown)."\n\n";
    }

    /**
     * Formate une formule mathématique
     */
    private function formatFormula($element): string
    {
        $displayMode = $element->getAttribute('display_mode', 'inline');

        if ($displayMode === 'block') {
            return "$$\n{$element->content}\n$$\n\n";
        }

        return "$\{{$element->content}}$\n\n";
    }

    /**
     * Formate un bloc de code
     */
    private function formatCode($element): string
    {
        $language = $element->getAttribute('language', '');

        return "```{$language}\n{$element->content}\n```\n\n";
    }
}
