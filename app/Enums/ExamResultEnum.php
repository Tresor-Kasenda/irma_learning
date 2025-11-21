<?php

declare(strict_types=1);

namespace App\Enums;

enum ExamResultEnum: string
{
    case PASSED = 'passed';

    case FAILED = 'failed';

    case PENDING = 'pending';

    public function getLabel(): string
    {
        return match ($this) {
            self::PASSED => 'Passé',
            self::FAILED => 'Échoué',
            self::PENDING => 'En attente',
        };
    }
}
