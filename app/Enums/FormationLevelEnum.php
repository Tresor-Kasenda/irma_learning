<?php

namespace App\Enums;

enum FormationLevelEnum: string
{
    case BEGINNER = 'Beginner';

    case INTERMEDIATE = 'Intermediate';

    case ADVANCED = 'Advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::BEGINNER => 'Débutant',
            self::INTERMEDIATE => 'Intermédiaire',
            self::ADVANCED => 'Avancé',
        };
    }
}
