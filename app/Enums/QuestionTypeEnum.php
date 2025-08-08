<?php

namespace App\Enums;

enum QuestionTypeEnum: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';

    case SINGLE_CHOICE = 'single_choice';

    case TRUE_FALSE = 'true_false';

    case TEXT = 'text';

    case ESSAY = 'essay';


    public function getLabel(): string
    {
        return match ($this) {
            self::MULTIPLE_CHOICE => 'Choix multiple',
            self::SINGLE_CHOICE => 'Choix unique',
            self::TRUE_FALSE => 'Vrai ou faux',
            self::TEXT => 'Texte',
            self::ESSAY => 'Essai',
        };
    }
}
