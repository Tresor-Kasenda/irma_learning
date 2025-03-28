<?php

declare(strict_types=1);

namespace App\Enums;

enum ChapterProgressEnum: string
{
    // 'completed', 'in_progress'
    case COMPLETED = 'completed';

    case IN_PROGRESS = 'in_progress';
}
