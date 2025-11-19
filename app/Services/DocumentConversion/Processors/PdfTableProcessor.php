<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use App\DTOs\DocumentElement;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Processeur pour détecter et extraire les tableaux depuis le contenu PDF
 */
final class PdfTableProcessor implements ContentProcessorInterface
{
    /**
     * Patterns pour détecter les tableaux dans le texte
     */
    private const array TABLE_INDICATORS = [
        '/^(?:[A-Za-zÀ-ÿ0-9\s\.\-\(\)]+\s*\|){2,}/',  // Colonnes séparées par |
        '/^(?:[A-Za-zÀ-ÿ0-9\s\.\-\(\)]+\t){2,}/',  // Colonnes séparées par tabulations
        '/^\s*[\|\+\-]{3,}/',              // Séparateurs de tableau
        '/^(?:\S+(?: \S+)*\s{2,}){2,}\S+(?: \S+)*/', // Colonnes séparées par 2+ espaces (détection visuelle)
    ];

    private const int MIN_COLUMNS = 2;

    private const int MIN_ROWS = 2;

    public function process(DocumentContent $content): DocumentContent
    {
        try {
            $tables = $this->detectTables($content->rawText);

            foreach ($tables as $table) {
                $content->addElement(new DocumentElement(
                    type: 'table',
                    content: [
                        'headers' => $table['headers'],
                        'rows' => $table['rows'],
                    ],
                    position: $table['position'],
                    attributes: [
                        'columnCount' => count($table['headers']),
                        'rowCount' => count($table['rows']),
                    ]
                ));
            }

            if (count($tables) > 0) {
                Log::info('Tables detected in PDF content', [
                    'count' => count($tables),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to detect tables', [
                'error' => $e->getMessage(),
            ]);
        }

        return $content;
    }

    /**
     * Détecte les tableaux dans le texte brut
     */
    private function detectTables(string $text): array
    {
        $lines = explode("\n", $text);
        $tables = [];
        $currentTable = null;
        $position = 0;

        foreach ($lines as $lineIndex => $line) {
            $lineLength = mb_strlen($line) + 1;

            if ($this->isTableLine($line)) {
                if ($currentTable === null) {
                    $currentTable = [
                        'headers' => [],
                        'rows' => [],
                        'position' => $position,
                        'startLine' => $lineIndex,
                    ];
                }

                $columns = $this->parseTableLine($line);

                if (empty($currentTable['headers']) && !$this->isSeparatorLine($line)) {
                    $currentTable['headers'] = $columns;
                } elseif (!$this->isSeparatorLine($line) && count($columns) > 0) {
                    if (empty($currentTable['headers']) || abs(count($currentTable['headers']) - count($columns)) <= 1) {
                        $currentTable['rows'][] = $columns;
                    }
                }
            } else {
                if ($currentTable !== null) {
                    if ($this->isValidTable($currentTable)) {
                        $tables[] = [
                            'headers' => $currentTable['headers'],
                            'rows' => $currentTable['rows'],
                            'position' => $currentTable['position'],
                        ];
                    }
                    $currentTable = null;
                }
            }

            $position += $lineLength;
        }

        if ($currentTable !== null && $this->isValidTable($currentTable)) {
            $tables[] = [
                'headers' => $currentTable['headers'],
                'rows' => $currentTable['rows'],
                'position' => $currentTable['position'],
            ];
        }

        return $tables;
    }

    /**
     * Vérifie si une ligne est une ligne de tableau
     */
    private function isTableLine(string $line): bool
    {
        $trimmed = mb_trim($line);

        if (empty($trimmed)) {
            return false;
        }

        foreach (self::TABLE_INDICATORS as $pattern) {
            if (preg_match($pattern, $trimmed)) {
                return true;
            }
        }

        return mb_substr_count($trimmed, '|') >= self::MIN_COLUMNS - 1 ||
            mb_substr_count($trimmed, "\t") >= self::MIN_COLUMNS - 1;
    }

    /**
     * Parse une ligne de tableau et extrait les colonnes
     */
    private function parseTableLine(string $line): array
    {
        $trimmed = mb_trim($line);

        $trimmed = mb_trim($trimmed, '| ');

        if (str_contains($trimmed, '|')) {
            $columns = explode('|', $trimmed);
        } elseif (str_contains($trimmed, "\t")) {
            $columns = explode("\t", $trimmed);
        } else {
            $columns = preg_split('/\s{2,}/', $trimmed);
        }

        return array_values(array_map(fn($col) => mb_trim($col), array_filter($columns, fn($col) => !empty(mb_trim($col)))));
    }

    /**
     * Vérifie si une ligne est un séparateur de tableau
     */
    private function isSeparatorLine(string $line): bool
    {
        $trimmed = mb_trim($line);

        return preg_match('/^[\|\+\-\s]+$/', $trimmed) && mb_strlen($trimmed) >= 3;
    }

    /**
     * Vérifie si un tableau détecté est valide
     */
    private function isValidTable(array $table): bool
    {
        return count($table['headers']) >= self::MIN_COLUMNS &&
            count($table['rows']) >= self::MIN_ROWS;
    }

    public function getPriority(): int
    {
        return 30;
    }
}
