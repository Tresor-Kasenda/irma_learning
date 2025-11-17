<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Extractors;

use App\Contracts\ElementExtractorInterface;
use App\DTOs\DocumentElement;

/**
 * Extracteur de tableaux depuis le texte brut
 */
final class TableExtractor implements ElementExtractorInterface
{
    public function extract(string $rawText, string $filePath, int &$position): array
    {
        $tables = [];
        $lines = explode("\n", $rawText);

        $currentTable = null;
        $inTable = false;

        foreach ($lines as $lineNumber => $line) {
            $line = mb_trim($line);

            // Détecte le début d'un tableau
            if ($this->isTableBorder($line)) {
                if (! $inTable) {
                    $inTable = true;
                    $currentTable = [
                        'rows' => [],
                        'position' => $position++,
                    ];
                }

                continue;
            }

            // Ligne de tableau détectée
            if ($inTable && $this->isTableRow($line)) {
                $cells = $this->parseTableRow($line);
                $currentTable['rows'][] = $cells;
            } else {
                // Fin du tableau
                if ($inTable && ! empty($currentTable['rows'])) {
                    $element = $this->createTableElement($currentTable);
                    $tables[] = $element;
                }
                $inTable = false;
                $currentTable = null;
            }
        }

        // Si le document se termine avec un tableau
        if ($inTable && $currentTable && ! empty($currentTable['rows'])) {
            $element = $this->createTableElement($currentTable);
            $tables[] = $element;
        }

        return $tables;
    }

    public function getElementType(): string
    {
        return 'table';
    }

    /**
     * Vérifie si la ligne est une bordure de tableau
     */
    private function isTableBorder(string $line): bool
    {
        // Bordure avec | et -
        if (preg_match('/^\s*[\|\+][-\+]+[\|\+]\s*$/', $line)) {
            return true;
        }

        // Bordure avec =
        if (preg_match('/^\s*[=]{3,}\s*$/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si la ligne est une ligne de tableau
     */
    private function isTableRow(string $line): bool
    {
        // Ligne avec séparateurs |
        if (preg_match('/^\s*\|.+\|\s*$/', $line)) {
            return true;
        }

        // Ligne avec multiples tabulations
        if (mb_substr_count($line, "\t") >= 2) {
            return true;
        }

        // Ligne avec multiples espaces (au moins 3 colonnes)
        if (preg_match('/\s{3,}.*\s{3,}/', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Parse une ligne de tableau et retourne les cellules
     */
    private function parseTableRow(string $line): array
    {
        // Cas 1: Séparateur |
        if (str_contains($line, '|')) {
            $cells = explode('|', mb_trim($line, '| '));

            return array_map('trim', $cells);
        }

        // Cas 2: Tabulations
        if (str_contains($line, "\t")) {
            $cells = explode("\t", $line);

            return array_map('trim', $cells);
        }

        // Cas 3: Espaces multiples
        $cells = preg_split('/\s{3,}/', $line);

        return array_map('trim', array_filter($cells));
    }

    /**
     * Crée un élément de tableau
     */
    private function createTableElement(array $tableData): DocumentElement
    {
        $rows = $tableData['rows'];
        $headers = array_shift($rows); // Première ligne = en-têtes

        return new DocumentElement(
            type: 'table',
            content: [
                'headers' => $headers ?? [],
                'rows' => $rows,
            ],
            position: $tableData['position'],
            attributes: [
                'column_count' => count($headers ?? []),
                'row_count' => count($rows),
            ],
        );
    }
}
