<?php

namespace App\Enums;

enum EnrollmentPaymentEnum: string
{
    case PENDING = 'pending';

    case PAID = 'paid';

    case FAILED = 'failed';

    case REFUNDED = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }
}
