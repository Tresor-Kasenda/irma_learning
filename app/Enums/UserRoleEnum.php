<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRoleEnum: string
{
    case STUDENT = 'STUDENT';

    case ADMIN = 'ADMIN';

    case ROOT = 'ROOT';

    case INSTRUCTOR = 'INSTRUCTOR';


    public function getLabel(): string
    {
        return match ($this) {
            self::STUDENT => 'Étudiant',
            self::ADMIN => 'Administrateur',
            self::ROOT => 'Root',
            self::INSTRUCTOR => 'Instructeur',
        };
    }
}
