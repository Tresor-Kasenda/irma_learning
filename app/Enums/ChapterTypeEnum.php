<?php

declare(strict_types=1);

namespace App\Enums;

enum ChapterTypeEnum: string
{
    case VIDEO = 'video';

    case TEXT = 'text';

    case PDF = 'pdf';

    case AUDIO = 'audio';

    public function getLabel(): string
    {
        return match ($this) {
            self::VIDEO => 'Vidéo',
            self::TEXT => 'Texte',
            self::PDF => 'PDF',
            self::AUDIO => 'Audio',
        };
    }
}
