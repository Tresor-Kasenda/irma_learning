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
            self::PENDING => "En attente",
            self::SUCCESS => "Réussi",
            self::FAILED => "Échoué",
            self::CANCELLED => "Annulé",
            self::REFUNDED => "Remboursé",
        };
    }
}
