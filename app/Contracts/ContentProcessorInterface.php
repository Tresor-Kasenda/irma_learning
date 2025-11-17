<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\DocumentContent;

/**
 * Interface pour le traitement et la transformation du contenu
 */
interface ContentProcessorInterface
{
    /**
     * Traite le contenu et retourne le contenu transformé
     */
    public function process(DocumentContent $content): DocumentContent;

    /**
     * Retourne la priorité du processor (plus petit = exécuté en premier)
     */
    public function getPriority(): int;
}
