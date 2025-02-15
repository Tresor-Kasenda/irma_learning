<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRoleEnum: string
{
    case STUDENT = 'STUDENT';
    case ADMIN = 'ADMIN';
    case ROOT = 'ROOT';
    case SUPPORT = 'SUPPORT';
    case MANAGER = 'MANAGER';

    public function getPrefix(): string
    {
        return match ($this) {
            self::STUDENT => 'ST',
            self::ADMIN => 'AD',
            self::ROOT => 'RT',
            self::SUPPORT => 'SU',
            self::MANAGER => 'MA',
        };
    }
}
