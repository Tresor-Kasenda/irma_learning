<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO pour les métadonnées d'un document
 */
final class DocumentMetadata
{
    public function __construct(
        public ?string $title = null,
        public ?string $author = null,
        public ?string $subject = null,
        public ?string $creator = null,
        public ?string $producer = null,
        public ?string $creationDate = null,
        public ?string $modificationDate = null,
        public int $pageCount = 0,
        public ?string $coverImage = null,
        public array $keywords = [],
        public array $customFields = [],
    ) {}

    /**
     * Créer depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            subject: $data['subject'] ?? null,
            creator: $data['creator'] ?? null,
            producer: $data['producer'] ?? null,
            creationDate: $data['creation_date'] ?? null,
            modificationDate: $data['modification_date'] ?? null,
            pageCount: $data['page_count'] ?? 0,
            coverImage: $data['cover_image'] ?? null,
            keywords: $data['keywords'] ?? [],
            customFields: $data['custom_fields'] ?? [],
        );
    }

    /**
     * Convertit les métadonnées en tableau
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'subject' => $this->subject,
            'creator' => $this->creator,
            'producer' => $this->producer,
            'creation_date' => $this->creationDate,
            'modification_date' => $this->modificationDate,
            'page_count' => $this->pageCount,
            'cover_image' => $this->coverImage,
            'keywords' => $this->keywords,
            'custom_fields' => $this->customFields,
        ];
    }
}
