<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRoleEnum: string
{
    case STUDENT = 'student';

    case ADMIN = 'admin';

    case ROOT = 'root';

    case INSTRUCTOR = 'instructor';


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
