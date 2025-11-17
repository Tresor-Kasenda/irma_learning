<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Processeur pour générer la table des matières
 */
final class TableOfContentsProcessor implements ContentProcessorInterface
{
    public function process(DocumentContent $content): DocumentContent
    {
        $toc = $this->generateTableOfContents($content->markdown);

        $content->tableOfContents = $toc;

        // Ajouter la table des matières au début du markdown
        if ($toc->isNotEmpty()) {
            $content->markdown = $this->prependTableOfContents($content->markdown, $toc);
        }

        return $content;
    }

    public function getPriority(): int
    {
        return 60; // Exécuté après le Markdown
    }

    /**
     * Génère la table des matières à partir du Markdown
     */
    private function generateTableOfContents(string $markdown): Collection
    {
        $lines = explode("\n", $markdown);
        $toc = [];

        foreach ($lines as $line) {
            // Détecte les titres Markdown (# Titre)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = mb_strlen($matches[1]);
                $title = mb_trim($matches[2]);

                // Nettoyer le titre (enlever les # éventuels)
                $title = mb_trim($title, '# ');

                // Générer l'ancre
                $anchor = $this->generateAnchor($title);

                $toc[] = [
                    'level' => $level,
                    'title' => $title,
                    'anchor' => $anchor,
                ];
            }
        }

        return collect($toc);
    }

    /**
     * Génère une ancre pour un titre
     */
    private function generateAnchor(string $title): string
    {
        // Convertir en slug
        $anchor = Str::slug($title, '-', 'fr');

        // Fallback si le slug est vide
        if (empty($anchor)) {
            $anchor = 'section-'.md5($title);
        }

        return $anchor;
    }

    /**
     * Ajoute la table des matières au début du document
     */
    private function prependTableOfContents(string $markdown, Collection $toc): string
    {
        $tocMarkdown = "## Table des matières\n\n";

        foreach ($toc as $item) {
            $indent = str_repeat('  ', $item['level'] - 1);
            $tocMarkdown .= "{$indent}- [{$item['title']}](#{$item['anchor']})\n";
        }

        $tocMarkdown .= "\n---\n\n";

        return $tocMarkdown.$markdown;
    }
}
