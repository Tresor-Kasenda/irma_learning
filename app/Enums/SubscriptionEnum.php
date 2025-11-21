<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionEnum: string
{
    case ACTIVE = 'active';

    case COMPLETED = 'completed';

    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::COMPLETED => 'Terminé',
            self::EXPIRED => 'Expiré',
        };
    }
}
