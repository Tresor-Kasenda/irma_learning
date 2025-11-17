<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO pour un élément du document (image, tableau, formule, etc.)
 */
final class DocumentElement
{
    public function __construct(
        public string $type,
        public mixed $content,
        public int $position,
        public array $attributes = [],
    ) {}

    /**
     * Créer depuis un tableau
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            content: $data['content'],
            position: $data['position'],
            attributes: $data['attributes'] ?? [],
        );
    }

    /**
     * Vérifie si l'élément a un attribut
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Récupère un attribut
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Définit un attribut
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Convertit en tableau
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'content' => $this->content,
            'position' => $this->position,
            'attributes' => $this->attributes,
        ];
    }
}
