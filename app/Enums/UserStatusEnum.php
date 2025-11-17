<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';

    case INACTIVE = 'inactive';

    case BANNED = 'suspended';

    /**
     * Get the prefix for the user status.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::INACTIVE => 'Inactif',
            self::BANNED => 'Suspendue',
        };
    }
}
