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
        return 50;
    }

    /**
     * Convertit le contenu en Markdown
     */
    private function convertToMarkdown(DocumentContent $content): string
    {
        $content->sortElementsByPosition();

        $markdown = '';
        $rawText = $content->rawText;
        $elements = $content->elements;
        $lastPosition = 0;

        foreach ($elements as $element) {
            if ($element->position > $lastPosition) {
                $textSegment = mb_substr($rawText, $lastPosition, $element->position - $lastPosition);
                $markdown .= $this->formatTextSegment($textSegment);
            }

            $markdown .= $this->formatElement($element);

            $contentLength = is_string($element->content) ? mb_strlen($element->content) : 0;
            $lastPosition = $element->position + $contentLength;
        }

        if ($lastPosition < mb_strlen($rawText)) {
            $textSegment = mb_substr($rawText, $lastPosition);
            $markdown .= $this->formatTextSegment($textSegment);
        }

        return mb_trim($markdown);
    }

    /**
     * Formate un segment de texte brut avec respect des sauts de ligne
     */
    private function formatTextSegment(string $text): string
    {
        $lines = explode("\n", $text);
        $formattedLines = [];
        $currentParagraph = [];

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            if (empty($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = '';

                continue;
            }

            // Vérifier si c'est une case à cocher
            if ($this->isCheckbox($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = $this->formatCheckbox($trimmedLine);

                continue;
            }

            if ($this->isBulletPoint($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                }
                // Nettoyer tous les caractères de puces Unicode et normaliser en tiret standard
                $cleaned = preg_replace('/^[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–—o]\s*/u', '', $trimmedLine);
                $formattedLines[] = '- '.mb_trim($cleaned);

                continue;
            }

            if ($this->isNumberedList($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = implode(' ', $currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = $trimmedLine;

                continue;
            }

            // Ajouter la ligne au paragraphe
            $currentParagraph[] = $trimmedLine;

            // Si la ligne se termine par une ponctuation forte, terminer le paragraphe
            if ($this->endsWithStrongPunctuation($trimmedLine)) {
                $formattedLines[] = implode(' ', $currentParagraph);
                $currentParagraph = [];
                $formattedLines[] = '';
            }
        }

        if (! empty($currentParagraph)) {
            $formattedLines[] = implode(' ', $currentParagraph);
        }

        return implode("\n", $formattedLines)."\n\n";
    }

    /**
     * Vérifie si une ligne se termine par une ponctuation forte
     */
    private function endsWithStrongPunctuation(string $line): bool
    {
        return (bool) preg_match('/[.!?]\s*$/', $line);
    }

    /**
     * Vérifie si une ligne est une liste à puces
     * Inclut tous les caractères Unicode de puces courants
     */
    private function isBulletPoint(string $line): bool
    {
        // Liste complète des caractères de puces Unicode
        return (bool) preg_match('/^[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–—o]\s+/u', $line);
    }

    /**
     * Vérifie si une ligne est une liste numérotée
     */
    private function isNumberedList(string $line): bool
    {
        return (bool) preg_match('/^\d+\.\s+/', $line);
    }

    /**
     * Vérifie si une ligne est une case à cocher
     */
    private function isCheckbox(string $line): bool
    {
        // Cases à cocher vides : □ ☐
        // Cases à cocher cochées : ☑ ☒ ✓ ✔ ✅
        return (bool) preg_match('/^[□☐☑☒✓✔✅]\s+/u', $line);
    }

    /**
     * Formate une case à cocher en syntaxe Markdown task list
     */
    private function formatCheckbox(string $line): string
    {
        // Cases vides
        if (preg_match('/^[□☐]\s+/u', $line)) {
            $cleaned = preg_replace('/^[□☐]\s*/u', '', $line);

            return '- [ ] '.mb_trim($cleaned);
        }

        // Cases cochées
        if (preg_match('/^[☑☒✓✔✅]\s+/u', $line)) {
            $cleaned = preg_replace('/^[☑☒✓✔✅]\s*/u', '', $line);

            return '- [x] '.mb_trim($cleaned);
        }

        return $line;
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

        $markdown[] = '| '.implode(' | ', $headers).' |';

        $markdown[] = '| '.implode(' | ', array_fill(0, count($headers), '---')).' |';

        foreach ($rows as $row) {
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
