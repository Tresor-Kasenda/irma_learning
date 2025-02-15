<?php

namespace App\Enums;

enum SubscriptionEnum: string
{
    // 'active', 'completed', 'expired'
    case ACTIVE = 'active';

    case COMPLETED = 'completed';

    case EXPIRED = 'expired';
}
