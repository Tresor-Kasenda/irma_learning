<?php

declare(strict_types=1);

namespace App\DTOs;

final class ChapterContentDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $durationMinutes,
        public readonly ?string $coverImage = null,
        public readonly ?string $markdownFile = null,
        public readonly ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            durationMinutes: (int) $data['duration_minutes'],
            coverImage: $data['cover_image'] ?? null,
            markdownFile: $data['markdown_file'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'duration_minutes' => $this->durationMinutes,
            'cover_image' => $this->coverImage,
            'markdown_file' => $this->markdownFile,
            'description' => $this->description,
        ];
    }
}
