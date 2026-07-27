<?php

declare(strict_types=1);

namespace App\Enums;

enum MobileMoneyCountryEnum: string
{
    case DRC = 'DRC';

    case KENYA = 'KE';

    case UGANDA = 'UG';

    public static function forCurrency(string $currency): ?self
    {
        foreach (self::cases() as $country) {
            if ($country->currency() === mb_strtoupper($currency)) {
                return $country;
            }
        }

        return null;
    }

    public function label(): string
    {
        return match ($this) {
            self::DRC => 'RDC (+243)',
            self::KENYA => 'Kenya (+254)',
            self::UGANDA => 'Ouganda (+256)',
        };
    }

    public function currency(): string
    {
        return match ($this) {
            self::DRC => 'CDF',
            self::KENYA => 'KES',
            self::UGANDA => 'UGX',
        };
    }

    public function dialCode(): string
    {
        return match ($this) {
            self::DRC => '+243',
            self::KENYA => '+254',
            self::UGANDA => '+256',
        };
    }

    public function minimumAmount(): int
    {
        return match ($this) {
            self::DRC => 2900,
            self::KENYA, self::UGANDA => 0,
        };
    }
}
