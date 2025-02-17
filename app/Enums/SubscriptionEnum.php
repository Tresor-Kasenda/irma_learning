<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionEnum: string
{
    // 'active', 'completed', 'expired'
    case ACTIVE = 'active';

    case COMPLETED = 'completed';

    case EXPIRED = 'expired';
}
