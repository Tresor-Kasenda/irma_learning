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
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FREE => 'Free',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }
}
