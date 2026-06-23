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
     * Formate un segment de texte brut en respectant la structure et la ponctuation.
     *
     * Gestion de la césure PDF : quand le PDF coupe un mot avec un tiret en fin de
     * ligne (ex: "connais-\nsance"), on réunit les deux parties sans espace et sans
     * tiret si la continuation commence par une minuscule. Les tirets intentionnels
     * (mots composés comme "Anglo-Saxon") sont préservés grâce au contrôle de la casse.
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
                    $formattedLines[] = $this->joinParagraphLines($currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = '';

                continue;
            }

            if ($this->isCheckbox($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = $this->joinParagraphLines($currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = $this->formatCheckbox($trimmedLine);

                continue;
            }

            if ($this->isBulletPoint($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = $this->joinParagraphLines($currentParagraph);
                    $currentParagraph = [];
                }
                $cleaned = preg_replace('/^[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–]\s*/u', '', $trimmedLine);
                $formattedLines[] = '- '.mb_trim($cleaned);

                continue;
            }

            if ($this->isNumberedList($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = $this->joinParagraphLines($currentParagraph);
                    $currentParagraph = [];
                }
                $formattedLines[] = $trimmedLine;

                continue;
            }

            if ($this->isLikelyTitle($trimmedLine)) {
                if (! empty($currentParagraph)) {
                    $formattedLines[] = $this->joinParagraphLines($currentParagraph);
                    $currentParagraph = [];
                    $formattedLines[] = '';
                }

                $formattedLines[] = $trimmedLine;
                $formattedLines[] = '';

                continue;
            }

            $currentParagraph[] = $trimmedLine;
        }

        if (! empty($currentParagraph)) {
            $formattedLines[] = $this->joinParagraphLines($currentParagraph);
        }

        return implode("\n", $formattedLines)."\n\n";
    }

    /**
     * Réunit les lignes d'un même paragraphe en gérant la césure PDF.
     *
     * Règles :
     * - Ligne se terminant par un tiret suivi d'une continuation en minuscule
     *   → tiret supprimé, mots réunis sans espace (césure de confort)
     * - Ligne se terminant par un tiret suivi d'une majuscule
     *   → tiret conservé, mots séparés par un espace (mot composé comme "Anglo-Saxon")
     * - Sinon → jointure normale avec un espace
     */
    private function joinParagraphLines(array $lines): string
    {
        $result = '';

        foreach ($lines as $i => $line) {
            if ($i === 0) {
                $result = $line;

                continue;
            }

            // Tiret en fin de ligne précédente + continuation en minuscule = césure PDF
            if (mb_substr($result, -1) === '-' && preg_match('/^\p{Ll}/u', $line)) {
                $result = mb_substr($result, 0, -1).$line;
            } elseif (preg_match('/^\pP/u', $line)) {
                // Ponctuation en début de ligne : pas d'espace (ex: "Bonjour" + "," → "Bonjour,")
                $result .= $line;
            } else {
                $result .= ' '.$line;
            }
        }

        return $result;
    }

    /**
     * Vérifie si une ligne est une case à cocher
     */
    private function isCheckbox(string $line): bool
    {
        return (bool) preg_match('/^[□☐☑☒✓✔✅]\s+/u', $line);
    }

    /**
     * Formate une case à cocher en syntaxe Markdown task list
     */
    private function formatCheckbox(string $line): string
    {
        if (preg_match('/^[□☐]\s+/u', $line)) {
            $cleaned = preg_replace('/^[□☐]\s*/u', '', $line);

            return '- [ ] '.mb_trim($cleaned);
        }

        if (preg_match('/^[☑☒✓✔✅]\s+/u', $line)) {
            $cleaned = preg_replace('/^[☑☒✓✔✅]\s*/u', '', $line);

            return '- [x] '.mb_trim($cleaned);
        }

        return $line;
    }

    /**
     * Vérifie si une ligne est une liste à puces.
     *
     * Note : la lettre "o" a été retirée de la classe — elle causait la conversion
     * erronée de lignes ordinaires commençant par "ou ", "on ", "or "… en puces.
     * Le tiret cadratin "—" a aussi été retiré : en français il introduit le dialogue,
     * pas une puce de liste.
     */
    private function isBulletPoint(string $line): bool
    {
        return (bool) preg_match('/^[•●○◦▪▫■□▸►▹‣⁃⁌⁍◘◙◉◎⦾⦿⚫⚪🔘🔲🔳➢➣➤➥➔→⇒➡\-\*\+−–]\s+/u', $line);
    }

    /**
     * Vérifie si une ligne est une liste numérotée
     */
    private function isNumberedList(string $line): bool
    {
        return (bool) preg_match('/^\d+\.\s+/', $line);
    }

    /**
     * Vérifie si une ligne est probablement un titre
     */
    private function isLikelyTitle(string $line): bool
    {
        if (preg_match('/^#{1,6}\s+/', $line)) {
            return true;
        }

        if (mb_strlen($line) > 150) {
            return false;
        }

        if (preg_match('/^(chapitre|chapter|partie|part|section|module)\s+(\d+|[ivxlcdm]+)/i', $line)) {
            return true;
        }

        if (preg_match('/^\d+(\.\d+)*\.?\s+[A-Z]/', $line)) {
            return true;
        }

        if (mb_strlen($line) > 5 && $line === mb_strtoupper($line) && ! preg_match('/^\d+$/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si une ligne se termine par une ponctuation forte
     */
    private function endsWithStrongPunctuation(string $line): bool
    {
        return (bool) preg_match('/[.!?]\s*$/', $line);
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
