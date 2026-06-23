<?php

declare(strict_types=1);

namespace App\Enums;

enum TrainingStatusEnum: string
{
    case PUBLISHED = 'published';

    case UNPUBLISHED = 'unpublished';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::PUBLISHED => 'Publier',
            self::UNPUBLISHED => 'Non publié',
            self::DRAFT => 'Brouillon',
            self::ARCHIVED => 'Archivé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLISHED => 'success',
            self::UNPUBLISHED => 'warning',
            self::DRAFT => 'info',
            self::ARCHIVED => 'danger',
        };
    }
}
