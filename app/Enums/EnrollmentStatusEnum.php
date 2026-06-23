<?php

declare(strict_types=1);

namespace App\Enums;

enum EnrollmentStatusEnum: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::COMPLETED => 'Terminé',
            self::SUSPENDED => 'Suspendu',
            self::CANCELLED => 'Annulé',
        };
    }
}
