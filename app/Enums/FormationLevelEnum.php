<?php

namespace App\Enums;

enum FormationLevelEnum: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function getLabel(): string
    {
        return match ($this) {
            self::BEGINNER => 'Débutant',
            self::INTERMEDIATE => 'Intermédiaire',
            self::ADVANCED => 'Avancé',
        };
    }
}
