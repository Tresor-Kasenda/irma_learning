<?php

declare(strict_types=1);

namespace App\Enums;

enum EnrollmentPaymentEnum: string
{
    case PENDING = 'pending';

    case PAID = 'paid';

    case FREE = 'free';

    case FAILED = 'failed';

    case REFUNDED = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PAID => 'Payé',
            self::FREE => 'Gratuit',
            self::FAILED => 'Échoué',
            self::REFUNDED => 'Remboursé',
        };
    }
}
