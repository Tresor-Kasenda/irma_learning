<?php

namespace App\Enums;

enum CertificateStatusEnum: string
{
    case ACTIVE = 'active';

    case REVOKED = 'revoked';

    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::REVOKED => 'Revoked',
            self::EXPIRED => 'Expired',
        };
    }
}
