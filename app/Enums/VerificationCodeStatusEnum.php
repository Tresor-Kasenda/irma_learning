<?php

namespace App\Enums;

enum VerificationCodeStatusEnum: string
{
    case Pending = 'pending';
    case Used = 'used';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Used => 'Utilisé',
            self::Expired => 'Expiré',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Used => 'success',
            self::Expired => 'danger',
        };
    }
}
