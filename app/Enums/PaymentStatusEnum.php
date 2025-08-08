<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';

    case SUCCESS = 'success';

    case FAILED = 'failed';

    case CANCELLED = 'cancelled';

    case REFUNDED = 'refunded';


    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }
}
