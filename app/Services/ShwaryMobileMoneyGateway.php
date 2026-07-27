<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MobileMoneyGateway;
use App\Data\MobileMoneyTransaction;
use App\Enums\MobileMoneyCountryEnum;
use LogicException;
use Shwary\Config;
use Shwary\DTOs\PaymentRequest;
use Shwary\DTOs\Transaction;
use Shwary\Enums\Country;
use Shwary\ShwaryClient;

final class ShwaryMobileMoneyGateway implements MobileMoneyGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.shwary.merchant_id'))
            && filled(config('services.shwary.merchant_key'));
    }

    public function initiate(
        int $amount,
        string $phoneNumber,
        MobileMoneyCountryEnum $country,
        string $callbackUrl,
    ): MobileMoneyTransaction {
        $transaction = $this->client()->createPayment(PaymentRequest::create(
            amount: $amount,
            phone: $phoneNumber,
            country: $this->shwaryCountry($country),
            callbackUrl: $callbackUrl,
        ));

        return $this->transaction($transaction);
    }

    public function parseWebhook(string $payload): MobileMoneyTransaction
    {
        return $this->transaction($this->client()->parseWebhook($payload));
    }

    private function client(): ShwaryClient
    {
        if (! $this->isConfigured()) {
            throw new LogicException('Les identifiants Shwary sont absents de la configuration.');
        }

        return new ShwaryClient(Config::fromArray([
            'merchant_id' => config('services.shwary.merchant_id'),
            'merchant_key' => config('services.shwary.merchant_key'),
            'sandbox' => config('services.shwary.sandbox'),
            'timeout' => config('services.shwary.timeout'),
        ]));
    }

    private function shwaryCountry(MobileMoneyCountryEnum $country): Country
    {
        return match ($country) {
            MobileMoneyCountryEnum::DRC => Country::DRC,
            MobileMoneyCountryEnum::KENYA => Country::KENYA,
            MobileMoneyCountryEnum::UGANDA => Country::UGANDA,
        };
    }

    private function transaction(Transaction $transaction): MobileMoneyTransaction
    {
        return new MobileMoneyTransaction(
            id: $transaction->id,
            referenceId: $transaction->referenceId,
            amount: $transaction->amount,
            currency: $transaction->currency,
            status: $transaction->status->value,
            phoneNumber: $transaction->recipientPhoneNumber,
            failureReason: $transaction->failureReason,
            completedAt: $transaction->completedAt?->format(DATE_ATOM),
        );
    }
}
