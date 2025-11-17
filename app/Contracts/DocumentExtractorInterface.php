<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\DocumentContent;

/**
 * Interface pour l'extraction de contenu depuis différents types de documents
 */
interface DocumentExtractorInterface
{
    /**
     * Vérifie si l'extracteur peut traiter ce type de fichier
     */
    public function supports(string $filePath): bool;

    /**
     * Extrait le contenu du document
     */
    public function extract(string $filePath, array $options = []): DocumentContent;

    /**
     * Retourne les types de fichiers supportés
     */
    public function getSupportedMimeTypes(): array;
}
