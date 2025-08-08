<?php

namespace App\Enums;

enum UserProgressEnum: string
{
    case COMPLETED = 'completed';

    case IN_PROGRESS = 'in_progress';

    case NOT_STARTED = 'not_started';


    public function getLabel(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::IN_PROGRESS => 'In Progress',
            self::NOT_STARTED => 'Not Started',
        };
    }
}
