<?php

namespace App\Enums;

enum EnrollmentStatusEnum: string
{
    case Active = 'active';

    case Completed = 'completed';

    case Suspended = 'suspended';

    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Suspended => 'Suspended',
            self::Cancelled => 'Cancelled',
        };
    }
}
