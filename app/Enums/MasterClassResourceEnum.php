<?php

declare(strict_types=1);

namespace App\Enums;

enum MasterClassResourceEnum: string
{
    case PDF = 'pdf';

    case VIDEO = 'video';

    case LINK = 'link';
}
