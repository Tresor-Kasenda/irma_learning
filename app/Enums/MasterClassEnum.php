<?php

declare(strict_types=1);

namespace App\Enums;

enum MasterClassEnum: string
{
    case UNPUBLISHED = 'unpublished';

    case PUBLISHED = 'published';
}
