<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;
use League\CommonMark\CommonMarkConverter;
use Smalot\PdfParser\Parser;

final class PdfConverterService
{
    protected CommonMarkConverter $markdownConverter;
    protected array $tocItems;
    private Parser $pdfParser;
    private int $currentPosition;

    public function __construct()
    {
        $this->pdfParser = new Parser;
        $this->markdownConverter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $this->tocItems = [];
        $this->currentPosition = 0;
    }

    public function convertToStructuredMarkdownAndHtml(string $path): array
    {
        try {
            $filePath = Storage::disk('public')->path($path);
            Log::info('Chemin du fichier PDF: ' . $filePath);

            if (!file_exists($filePath)) {
                throw new Exception("Fichier non trouvé à l'emplacement: " . $filePath);
            }

            $this->currentPosition = 0;
            $this->tocItems = [];
            $elements = [];

            $pdf = $this->pdfParser->parseFile($filePath);
            $rawText = $pdf->getText();
            Log::info('Contenu brut du PDF: ' . mb_substr($rawText, 0, 500)); // Affiche les 500 premiers caractères

            $elements = array_merge(
                $this->extractHeaders($rawText),
                $this->extractLinks($rawText),
                $this->extractTables($rawText),
                $this->extractMathFormulas($rawText)
            );

            $imageElements = $this->processImages($filePath, $rawText);
            $elements = array_merge($elements, $imageElements);

            usort($elements, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            $markdown = $this->generateMarkdown($rawText, $elements);
            Log::info('Markdown généré: ' . mb_substr($markdown, 0, 500)); // Affiche les 500 premiers caractères

            return [
                'markdown' => $markdown,
                'elements' => $elements,
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la conversion PDF: ' . $e->getMessage());
            throw new Exception('Échec de la conversion PDF: ' . $e->getMessage());
        }
    }

    private function extractHeaders(string $text): array
    {
        $headers = [];
        $lines = explode("\n", $text);
        $previousLine = '';

        foreach ($lines as $line) {
            $line = mb_trim($line);
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^[=\-]{3,}$/', $line) && !empty($previousLine)) {
                $level = $line[0] === '=' ? 1 : 2;
                $this->currentPosition++;

                $header = [
                    'type' => 'header',
                    'content' => $previousLine,
                    'level' => $level,
                    'position' => $this->currentPosition,
                ];

                $headers[] = $header;
                $this->tocItems[] = $header;
            } elseif (preg_match('/^(\d+\.?)+(\s+.+)$/', $line, $matches)) {
                $level = mb_substr_count($matches[1], '.');
                $this->currentPosition++;

                $header = [
                    'type' => 'header',
                    'content' => mb_trim($matches[2]),
                    'level' => $level,
                    'position' => $this->currentPosition,
                ];

                $headers[] = $header;
                $this->tocItems[] = $header;
            } elseif (preg_match('/^([a-zA-Z]+\.?)+(\s+.+)$/', $line, $matches)) {
                $level = mb_substr_count($matches[1], '.');
                $this->currentPosition++;

                $header = [
                    'type' => 'header',
                    'content' => mb_trim($matches[2]),
                    'level' => $level,
                    'position' => $this->currentPosition,
                ];

                $headers[] = $header;
                $this->tocItems[] = $header;
            }

            $previousLine = $line;
        }

        return $headers;
    }

    private function extractLinks(string $rawText): array
    {
        $links = [];
        $pattern = '/(https?:\/\/[^\s\)\"]+)/i';

        if (preg_match_all($pattern, $rawText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $url = $match[0];
                $this->currentPosition++;

                $start = max(0, $match[1] - 50);
                $length = min(mb_strlen($rawText) - $start, 100);
                $context = mb_substr($rawText, $start, $length);

                $linkText = preg_replace('/^\S*' . preg_quote($url, '/') . '\S*/', '', $context);
                $linkText = mb_trim($linkText);

                if (empty($linkText)) {
                    $linkText = $url;
                }

                $links[] = [
                    'type' => 'link',
                    'content' => $linkText,
                    'url' => $url,
                    'position' => $this->currentPosition,
                ];
            }
        }

        return $links;
    }

    private function extractTables(string $text): array
    {
        $tables = [];
        $lines = explode("\n", $text);
        $currentTable = [];
        $inTable = false;

        foreach ($lines as $line) {
            $line = mb_trim($line);

            if (preg_match('/^\s*[\|\+][-\+]+[\|\+]\s*$/', $line)) {
                if (!$inTable) {
                    $inTable = true;
                    $this->currentPosition++;
                    $currentTable = [
                        'type' => 'table',
                        'content' => [],
                        'position' => $this->currentPosition,
                    ];
                }

                continue;
            }

            if ($inTable) {
                if (preg_match('/^\s*\|.+\|\s*$/', $line)) {
                    $cells = array_map('trim', explode('|', mb_trim($line, '| ')));
                    $currentTable['content'][] = $cells;
                } else {
                    if (!empty($currentTable['content'])) {
                        $tables[] = $currentTable;
                    }
                    $inTable = false;
                }
            }
        }

        return $tables;
    }

    private function extractMathFormulas(string $text): array
    {
        $formulas = [];
        $pattern = '/\$\$(.*?)\$\$|\$(.*?)\$/s';

        if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $this->currentPosition++;
                $formulas[] = [
                    'type' => 'math',
                    'content' => $match[0],
                    'position' => $this->currentPosition,
                ];
            }
        }

        return $formulas;
    }

    private function processImages(string $filePath, string $rawText): array
    {
        $images = [];
        $imageReferences = [];
        $pattern = '/(Figure|Image)\s+\d+/';

        if (preg_match_all($pattern, $rawText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $imageReferences[] = [
                    'reference' => $match[0],
                    'position' => $match[1],
                ];
            }
        }

        try {
            $imagick = new Imagick;
            $imagick->readImage($filePath);

            foreach ($imagick as $pageNum => $page) {
                $page->setIteratorIndex(0);
                $numImages = $page->getNumberImages();

                for ($i = 0; $i < $numImages; $i++) {
                    $page->setIteratorIndex($i);
                    $imageBlob = $page->getImageBlob();

                    if ($imageBlob !== false) {
                        $imagePath = sprintf('images/pdf/page_%d_image_%d.png', $pageNum + 1, $i + 1);
                        Storage::put($imagePath, $imageBlob);

                        $imagePosition = null;
                        foreach ($imageReferences as $reference) {
                            if (str_contains($reference['reference'], (string)($i + 1))) {
                                $imagePosition = $reference['position'];
                                break;
                            }
                        }

                        $images[] = [
                            'type' => 'image',
                            'path' => $imagePath,
                            'position' => $imagePosition ?? $this->currentPosition++,
                            'page' => $pageNum + 1,
                            'alt' => sprintf('Image %d de la page %d', $i + 1, $pageNum + 1),
                        ];
                    }
                }
            }

            $imagick->clear();
            $imagick->destroy();

        } catch (Exception $e) {
            Log::warning('Erreur lors du traitement des images: ' . $e->getMessage());
        }

        return $images;
    }

    private function generateMarkdown(string $rawText, array $elements): string
    {
        usort($elements, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        $markdown = '';
        $lastPosition = 0;

        foreach ($elements as $element) {
            $markdown .= mb_substr($rawText, $lastPosition, $element['position'] - $lastPosition);

            switch ($element['type']) {
                case 'header':
                    if (isset($element['content'])) {
                        $markdown .= str_repeat('#', $element['level']) . ' ' . $element['content'] . "\n\n";
                    }
                    break;

                case 'link':
                    if (isset($element['content'], $element['url'])) {
                        $markdown .= sprintf('[%s](%s)', $element['content'], $element['url']) . "\n\n";
                    }
                    break;

                case 'table':
                    if (isset($element['content'])) {
                        $markdown .= $this->renderTable($element['content']) . "\n\n";
                    }
                    break;

                case 'math':
                    if (isset($element['content'])) {
                        $markdown .= $element['content'] . "\n\n";
                    }
                    break;

                case 'image':
                    if (isset($element['path'], $element['alt'])) {
                        $markdown .= sprintf(
                                '![%s](%s)',
                                $element['alt'],
                                Storage::url($element['path'])
                            ) . "\n\n";
                    }
                    break;
            }

            $lastPosition = $element['position'] + (isset($element['content']) ? mb_strlen($element['content']) : 0);
        }

        $markdown .= mb_substr($rawText, $lastPosition);

        return mb_trim($markdown);
    }

    private function renderTable(array $tableData): string
    {
        if (empty($tableData)) {
            return '';
        }

        $markdown = [];
        $headers = array_shift($tableData);

        $markdown[] = '| ' . implode(' | ', $headers) . ' |';

        $markdown[] = '| ' . implode(' | ', array_fill(0, count($headers), '---')) . ' |';

        foreach ($tableData as $row) {
            $markdown[] = '| ' . implode(' | ', $row) . ' |';
        }

        return implode("\n", $markdown);
    }
}
