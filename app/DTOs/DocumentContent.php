<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Collection;

/**
 * DTO pour le contenu d'un document
 */
final class DocumentContent
{
    public function __construct(
        public string $rawText = '',
        public string $markdown = '',
        public DocumentMetadata $metadata = new DocumentMetadata(),
        public Collection $elements = new Collection(),
        public Collection $tableOfContents = new Collection(),
        public array $options = [],
    ) {}

    /**
     * Ajoute un élément au document
     */
    public function addElement(DocumentElement $element): self
    {
        $this->elements->push($element);

        return $this;
    }

    /**
     * Filtre les éléments par type
     */
    public function getElementsByType(string $type): Collection
    {
        return $this->elements->filter(fn (DocumentElement $el) => $el->type === $type);
    }

    /**
     * Trie les éléments par position
     */
    public function sortElementsByPosition(): self
    {
        $this->elements = $this->elements->sortBy('position')->values();

        return $this;
    }

    /**
     * Clone le DTO
     */
    public function clone(): self
    {
        return new self(
            rawText: $this->rawText,
            markdown: $this->markdown,
            metadata: clone $this->metadata,
            elements: clone $this->elements,
            tableOfContents: clone $this->tableOfContents,
            options: $this->options,
        );
    }

    /**
     * Retourne les statistiques du document
     */
    public function getStats(): array
    {
        return [
            'raw_text_length' => mb_strlen($this->rawText),
            'markdown_length' => mb_strlen($this->markdown),
            'word_count' => str_word_count(strip_tags($this->markdown)),
            'elements_count' => $this->elements->count(),
            'images_count' => $this->getElementsByType('image')->count(),
            'tables_count' => $this->getElementsByType('table')->count(),
            'formulas_count' => $this->getElementsByType('formula')->count(),
            'code_blocks_count' => $this->getElementsByType('code')->count(),
            'toc_items_count' => $this->tableOfContents->count(),
        ];
    }
}
