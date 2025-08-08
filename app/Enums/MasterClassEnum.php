<?php

declare(strict_types=1);

namespace App\Enums;

enum MasterClassEnum: string
{
    case UNPUBLISHED = 'unpublished';

    case PUBLISHED = 'published';

    case ARCHIVED = 'archived';

    public static function getAll(): array
    {
        return [
            self::UNPUBLISHED,
            self::PUBLISHED,
            self::ARCHIVED,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::UNPUBLISHED => 'Unpublished',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPUBLISHED => 'warning',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'danger',
        };
    }
}
