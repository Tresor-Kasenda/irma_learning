<?php

namespace App\Enums;

enum ChapterTypeEnum: string
{
    case VIDEO = 'video';

    case TEXT = 'text';

    case PDF = 'pdf';

    public function getLabel(): string
    {
        return match ($this) {
            self::VIDEO => 'Vidéo',
            self::TEXT => 'Texte',
            self::PDF => 'PDF',
        };
    }
}
