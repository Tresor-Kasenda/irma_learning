<?php

namespace App\Enums;

enum ExamAttemptEnum: string
{
    case IN_PROGRESS = 'in_progress';

    case COMPLETED = 'completed';

    case FAILED = 'failed';

    case CANCELLED = 'cancelled';


    public function getLabel(): string
    {
        return match ($this) {
            self::IN_PROGRESS => "En cours",
            self::COMPLETED => "Terminé",
            self::FAILED => "Échoué",
            self::CANCELLED => "Annulé",
        };
    }
}
