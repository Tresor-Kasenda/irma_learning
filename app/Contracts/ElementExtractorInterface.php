<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface pour l'extraction d'éléments spécifiques (images, tableaux, formules, etc.)
 */
interface ElementExtractorInterface
{
    /**
     * Extrait les éléments du texte brut
     *
     * @return array Liste des éléments extraits avec leurs positions
     */
    public function extract(string $rawText, string $filePath, int &$position): array;

    /**
     * Retourne le type d'élément extrait (image, table, formula, etc.)
     */
    public function getElementType(): string;
}
