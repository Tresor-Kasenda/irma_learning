<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MobileMoneyGateway;
use App\Data\MobileMoneyTransaction;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\MobileMoneyCountryEnum;
use App\Models\ApplicationSetting;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Throwable;

final class MobileMoneyCheckoutService
{
    public function __construct(
        private readonly MobileMoneyGateway $gateway,
        private readonly LearnerNotificationService $notifications,
    ) {}

    /**
     * @return array{available: bool, currency: string, countries: array<int, array{value: string, label: string, dial_code: string}>, message: string|null}
     */
    public function paymentOptions(): array
    {
        $currency = mb_strtoupper(ApplicationSetting::current()->default_currency);
        $country = MobileMoneyCountryEnum::forCurrency($currency);

        if (! $this->gateway->isConfigured()) {
            return [
                'available' => false,
                'currency' => $currency,
                'countries' => [],
                'message' => 'Le Mobile Money sera disponible dès que les identifiants Shwary auront été configurés.',
            ];
        }

        if (! $country) {
            return [
                'available' => false,
                'currency' => $currency,
                'countries' => [],
                'message' => sprintf('Le Mobile Money Shwary accepte les paiements en CDF, KES ou UGX. La devise actuelle est %s.', $currency),
            ];
        }

        return [
            'available' => true,
            'currency' => $currency,
            'countries' => [[
                'value' => $country->value,
                'label' => $country->label(),
                'dial_code' => $country->dialCode(),
            ]],
            'message' => null,
        ];
    }

    /**
     * @throws Throwable
     */
    public function startPayment(
        User $user,
        Formation $formation,
        MobileMoneyCountryEnum $country,
        string $phoneNumber,
    ): Enrollment {
        if (! $this->gateway->isConfigured()) {
            throw new LogicException('Le paiement par Mobile Money n’est pas encore configuré.');
        }

        [$amount, $currency] = $this->mobileMoneyAmount($formation, $country);
        $enrollment = $this->prepareEnrollment($user, $formation, $country, $phoneNumber, $amount, $currency);

        if ($this->isWaitingForSamePayment($enrollment, $country, $phoneNumber)) {
            return $enrollment;
        }

        try {
            $transaction = $this->gateway->initiate(
                amount: $amount,
                phoneNumber: $phoneNumber,
                country: $country,
                callbackUrl: $this->callbackUrl(),
            );
        } catch (Throwable $exception) {
            $enrollment->update([
                'payment_status' => EnrollmentPaymentEnum::FAILED,
                'payment_gateway_response' => [
                    'status' => 'failed_to_initiate',
                ],
            ]);

            throw $exception;
        }

        $enrollment->update([
            'payment_transaction_id' => $transaction->id,
            'payment_gateway_response' => $this->gatewayResponse($transaction, $country, 'initiation', $phoneNumber),
        ]);

        if ($transaction->isCompleted() || $transaction->isFailed()) {
            $this->processTransaction($transaction, 'initiation');
        }

        return $enrollment->refresh();
    }

    /**
     * @throws Throwable
     */
    public function handleWebhook(string $payload): void
    {
        $this->processTransaction($this->gateway->parseWebhook($payload), 'webhook');
    }

    public function hasValidWebhookToken(string $token): bool
    {
        return hash_equals($this->webhookToken(), $token);
    }

    private function webhookToken(): string
    {
        $appKey = config('app.key');

        if (! is_string($appKey) || $appKey === '') {
            throw new LogicException('La clé de l’application est absente de la configuration.');
        }

        return hash_hmac('sha256', 'shwary-webhook', $appKey);
    }

    private function callbackUrl(): string
    {
        $callbackUrl = route('shwary.webhook', ['token' => $this->webhookToken()]);
        $scheme = parse_url($callbackUrl, PHP_URL_SCHEME);

        if ($scheme !== 'https') {
            throw new LogicException('Le paiement par Mobile Money requiert une APP_URL publique en HTTPS pour recevoir la confirmation.');
        }

        return $callbackUrl;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function mobileMoneyAmount(Formation $formation, MobileMoneyCountryEnum $country): array
    {
        $amount = (float) ($formation->price ?? 0);
        $currency = mb_strtoupper(ApplicationSetting::current()->default_currency);

        if ($currency !== $country->currency()) {
            throw new InvalidArgumentException(sprintf('Le Mobile Money %s doit être réglé en %s.', $country->label(), $country->currency()));
        }

        if ($amount <= 0 || round($amount, 0) !== $amount) {
            throw new InvalidArgumentException('Le prix de la formation doit être un montant entier pour le paiement Mobile Money.');
        }

        $integerAmount = (int) $amount;

        if ($integerAmount <= $country->minimumAmount()) {
            throw new InvalidArgumentException(sprintf('Le montant minimal pour %s est de %d %s.', $country->label(), $country->minimumAmount() + 1, $currency));
        }

        return [$integerAmount, $currency];
    }

    /**
     * @throws Throwable
     */
    private function prepareEnrollment(
        User $user,
        Formation $formation,
        MobileMoneyCountryEnum $country,
        string $phoneNumber,
        int $amount,
        string $currency,
    ): Enrollment {
        return DB::transaction(function () use ($user, $formation, $country, $phoneNumber, $amount, $currency): Enrollment {
            $enrollment = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('formation_id', $formation->id)
                ->lockForUpdate()
                ->first();

            if ($enrollment?->payment_status === EnrollmentPaymentEnum::PAID) {
                throw new LogicException('Cette formation est déjà payée.');
            }

            if ($enrollment && $this->isWaitingForSamePayment($enrollment, $country, $phoneNumber)) {
                return $enrollment;
            }

            $attributes = [
                'status' => EnrollmentStatusEnum::ACTIVE,
                'payment_status' => EnrollmentPaymentEnum::PENDING,
                'payment_method' => 'mobile_money',
                'payment_transaction_id' => null,
                'stripe_checkout_session_id' => null,
                'payment_gateway' => 'shwary',
                'payment_gateway_response' => [
                    'country' => $country->value,
                    'recipient_phone_number' => $phoneNumber,
                    'status' => 'pending_initiation',
                ],
                'amount_paid' => $amount,
                'currency' => $currency,
                'payment_processed_at' => null,
                'progress_percentage' => 0,
            ];

            if ($enrollment) {
                $enrollment->update($attributes);

                return $enrollment;
            }

            return $user->enrollments()->create([
                ...$attributes,
                'formation_id' => $formation->id,
                'enrollment_date' => now(),
            ]);
        }, attempts: 3);
    }

    private function isWaitingForSamePayment(
        Enrollment $enrollment,
        MobileMoneyCountryEnum $country,
        string $phoneNumber,
    ): bool {
        $response = $enrollment->payment_gateway_response;

        return $enrollment->payment_status === EnrollmentPaymentEnum::PENDING
            && $enrollment->payment_gateway === 'shwary'
            && filled($enrollment->payment_transaction_id)
            && is_array($response)
            && ($response['country'] ?? null) === $country->value
            && ($response['recipient_phone_number'] ?? null) === $phoneNumber;
    }

    /**
     * @throws Throwable
     */
    private function processTransaction(MobileMoneyTransaction $transaction, string $receivedVia): void
    {
        DB::transaction(function () use ($transaction, $receivedVia): void {
            $enrollment = Enrollment::query()
                ->where('payment_gateway', 'shwary')
                ->where('payment_transaction_id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($enrollment->payment_status === EnrollmentPaymentEnum::PAID) {
                return;
            }

            $country = MobileMoneyCountryEnum::tryFrom((string) (($enrollment->payment_gateway_response ?? [])['country'] ?? ''));

            if (! $country) {
                throw new InvalidArgumentException('La transaction Mobile Money ne référence aucun pays valide.');
            }

            $this->ensureTransactionMatchesEnrollment($transaction, $enrollment, $country);
            $response = $this->gatewayResponse($transaction, $country, $receivedVia);

            if ($transaction->isCompleted()) {
                $enrollment->markAsPaid([
                    'transaction_id' => $transaction->id,
                    'method' => 'mobile_money',
                    'gateway' => 'shwary',
                    'gateway_response' => $response,
                ]);

                $this->notifications->paymentConfirmed($enrollment);

                return;
            }

            if ($transaction->isFailed()) {
                $enrollment->update([
                    'payment_status' => EnrollmentPaymentEnum::FAILED,
                    'payment_gateway_response' => $response,
                ]);
            }
        }, attempts: 3);
    }

    private function ensureTransactionMatchesEnrollment(
        MobileMoneyTransaction $transaction,
        Enrollment $enrollment,
        MobileMoneyCountryEnum $country,
    ): void {
        if ($transaction->amount !== (int) round((float) $enrollment->amount_paid)) {
            throw new InvalidArgumentException('Le montant reçu du Mobile Money ne correspond pas à l’inscription.');
        }

        if (mb_strtoupper($transaction->currency) !== mb_strtoupper((string) $enrollment->currency)
            || mb_strtoupper($transaction->currency) !== $country->currency()) {
            throw new InvalidArgumentException('La devise reçue du Mobile Money ne correspond pas à l’inscription.');
        }

        $response = $enrollment->payment_gateway_response ?? [];
        $referenceId = is_array($response) ? ($response['reference_id'] ?? null) : null;
        $phoneNumber = is_array($response) ? ($response['recipient_phone_number'] ?? null) : null;

        if (is_string($referenceId) && $referenceId !== '' && $transaction->referenceId !== $referenceId) {
            throw new InvalidArgumentException('La référence Mobile Money ne correspond pas à l’inscription.');
        }

        if (is_string($phoneNumber) && $phoneNumber !== '' && $transaction->phoneNumber !== $phoneNumber) {
            throw new InvalidArgumentException('Le numéro Mobile Money ne correspond pas à l’inscription.');
        }
    }

    /**
     * @return array<string, string|int|null>
     */
    private function gatewayResponse(
        MobileMoneyTransaction $transaction,
        MobileMoneyCountryEnum $country,
        string $receivedVia,
        ?string $requestedPhoneNumber = null,
    ): array {
        return [
            'shwary_transaction_id' => $transaction->id,
            'reference_id' => $transaction->referenceId,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'currency' => mb_strtoupper($transaction->currency),
            'country' => $country->value,
            'recipient_phone_number' => $requestedPhoneNumber ?? $transaction->phoneNumber,
            'failure_reason' => $transaction->failureReason,
            'completed_at' => $transaction->completedAt,
            'received_via' => $receivedVia,
        ];
    }
}
