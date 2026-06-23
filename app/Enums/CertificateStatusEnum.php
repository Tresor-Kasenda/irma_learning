<?php

declare(strict_types=1);

namespace App\Enums;

enum CertificateStatusEnum: string
{
    case ACTIVE = 'active';

    case REVOKED = 'revoked';

    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::REVOKED => 'Révoqué',
            self::EXPIRED => 'Périmé',
        };
    }
}
