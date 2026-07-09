<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRoleEnum: string
{
    case STUDENT = 'student';

    case ADMIN = 'admin';

    case ROOT = 'root';

    case INSTRUCTOR = 'instructor';

    /**
     * Roles an actor is allowed to assign to another user. Only root can grant root.
     *
     * @return list<self>
     */
    public static function assignable(bool $actorIsRoot): array
    {
        return $actorIsRoot
            ? self::cases()
            : array_values(array_filter(self::cases(), fn (self $role): bool => $role !== self::ROOT));
    }

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
