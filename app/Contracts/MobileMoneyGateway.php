<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\MobileMoneyTransaction;
use App\Enums\MobileMoneyCountryEnum;

interface MobileMoneyGateway
{
    public function isConfigured(): bool;

    public function initiate(
        int $amount,
        string $phoneNumber,
        MobileMoneyCountryEnum $country,
        string $callbackUrl,
    ): MobileMoneyTransaction;

    public function parseWebhook(string $payload): MobileMoneyTransaction;
}
