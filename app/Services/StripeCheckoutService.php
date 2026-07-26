<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\ApplicationSetting;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Throwable;

final class StripeCheckoutService
{
    /**
     * @var array<int, string>
     */
    private const array ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    public function __construct(private readonly LearnerNotificationService $notifications)
    {
    }

    /**
     * @throws ApiErrorException|Throwable
     */
    public function startCheckout(User $user, Formation $formation): ?string
    {
        $amount = (float)($formation->price ?? 0);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Une formation gratuite ne peut pas être payée par Stripe.');
        }

        $currency = mb_strtoupper(ApplicationSetting::current()->default_currency);
        $enrollment = $this->pendingEnrollment($user, $formation, $currency, $amount);
        $stripe = $this->stripeClient();

        if ($enrollment->stripe_checkout_session_id) {
            $existingSession = $stripe->checkout->sessions->retrieve($enrollment->stripe_checkout_session_id);
            $existingCheckoutUrl = (string)($existingSession->url ?? '');

            if ($existingSession->status === 'open' && $existingCheckoutUrl !== '') {
                return $existingCheckoutUrl;
            }

            if ($existingSession->status === 'complete') {
                $this->completeCheckout($existingSession->toArray());

                if ($enrollment->refresh()->payment_status === EnrollmentPaymentEnum::PAID) {
                    return null;
                }
            }
        }

        $this->resetPendingEnrollment($enrollment, $currency, $amount);

        $checkoutSession = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'client_reference_id' => (string)$enrollment->id,
            'customer_email' => $user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => mb_strtolower($currency),
                    'product_data' => [
                        'name' => $formation->title,
                    ],
                    'unit_amount' => $this->amountInMinorUnit($amount, $currency),
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'enrollment_id' => (string)$enrollment->id,
                'formation_id' => (string)$formation->id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'enrollment_id' => (string)$enrollment->id,
                    'formation_id' => (string)$formation->id,
                ],
            ],
            'success_url' => route('student.payment.success', $formation->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('student.payment.create', $formation->id),
        ]);

        $checkoutSessionId = (string)($checkoutSession->id ?? '');
        $checkoutUrl = (string)($checkoutSession->url ?? '');

        if ($checkoutSessionId === '' || $checkoutUrl === '') {
            throw new LogicException('Stripe n\'a pas renvoyé de session de paiement valide.');
        }

        $enrollment->update([
            'stripe_checkout_session_id' => $checkoutSessionId,
            'payment_gateway_response' => [
                'checkout_session_id' => $checkoutSessionId,
                'checkout_status' => $checkoutSession->status,
            ],
        ]);

        return $checkoutUrl;
    }

    /**
     * @throws Throwable
     */
    private function pendingEnrollment(User $user, Formation $formation, string $currency, float $amount): Enrollment
    {
        return DB::transaction(function () use ($user, $formation, $currency, $amount): Enrollment {
            $enrollment = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('formation_id', $formation->id)
                ->lockForUpdate()
                ->first();

            if ($enrollment?->payment_status === EnrollmentPaymentEnum::PAID) {
                throw new LogicException('Cette formation est déjà payée.');
            }

            if ($enrollment) {
                if (!$enrollment->stripe_checkout_session_id) {
                    $this->resetPendingEnrollment($enrollment, $currency, $amount);
                }

                return $enrollment;
            }

            return $user->enrollments()->create([
                'status' => EnrollmentStatusEnum::ACTIVE,
                'payment_status' => EnrollmentPaymentEnum::PENDING,
                'payment_method' => 'card',
                'payment_gateway' => 'stripe',
                'amount_paid' => $amount,
                'currency' => $currency,
                'progress_percentage' => 0,
                'formation_id' => $formation->id,
                'enrollment_date' => now(),
            ]);
        }, attempts: 3);
    }

    private function resetPendingEnrollment(Enrollment $enrollment, string $currency, float $amount): void
    {
        $enrollment->update([
            'status' => EnrollmentStatusEnum::ACTIVE,
            'payment_status' => EnrollmentPaymentEnum::PENDING,
            'payment_method' => 'card',
            'payment_transaction_id' => null,
            'stripe_checkout_session_id' => null,
            'payment_gateway' => 'stripe',
            'payment_gateway_response' => null,
            'amount_paid' => $amount,
            'currency' => $currency,
            'payment_processed_at' => null,
            'progress_percentage' => 0,
        ]);
    }

    private function stripeClient(): StripeClient
    {
        $secret = config('services.stripe.secret');

        if (!is_string($secret) || $secret === '') {
            throw new LogicException('La clé secrète Stripe est absente de la configuration.');
        }

        return new StripeClient($secret);
    }

    /**
     * @param array<string, mixed> $checkoutSession
     *
     * @throws Throwable
     */
    private function completeCheckout(array $checkoutSession, ?string $eventId = null): void
    {
        if (($checkoutSession['payment_status'] ?? null) !== 'paid') {
            return;
        }

        DB::transaction(function () use ($checkoutSession, $eventId): void {
            $enrollment = $this->enrollmentForCheckout($checkoutSession);

            if ($enrollment->payment_status === EnrollmentPaymentEnum::PAID) {
                return;
            }

            $this->ensureCheckoutMatchesEnrollment($checkoutSession, $enrollment);

            $enrollment->markAsPaid([
                'transaction_id' => $this->stringValue($checkoutSession['payment_intent'] ?? null),
                'method' => 'card',
                'gateway' => 'stripe',
                'gateway_response' => $this->gatewayResponse($checkoutSession, $eventId),
            ]);

            $this->notifications->paymentConfirmed($enrollment);
        }, attempts: 3);
    }

    /**
     * @param array<string, mixed> $checkoutSession
     */
    private function enrollmentForCheckout(array $checkoutSession): Enrollment
    {
        $metadata = $checkoutSession['metadata'] ?? [];
        $enrollmentId = is_array($metadata) ? filter_var($metadata['enrollment_id'] ?? null, FILTER_VALIDATE_INT) : false;

        if ($enrollmentId === false) {
            throw new InvalidArgumentException('La session Stripe ne référence aucune inscription valide.');
        }

        return Enrollment::query()->lockForUpdate()->findOrFail($enrollmentId);
    }

    /**
     * @param array<string, mixed> $checkoutSession
     */
    private function ensureCheckoutMatchesEnrollment(array $checkoutSession, Enrollment $enrollment): void
    {
        $metadata = $checkoutSession['metadata'] ?? [];

        if (!is_array($metadata) || (string)($metadata['formation_id'] ?? '') !== (string)$enrollment->formation_id) {
            throw new InvalidArgumentException('La session Stripe ne correspond pas à la formation inscrite.');
        }

        if (mb_strtolower((string)($checkoutSession['currency'] ?? '')) !== mb_strtolower((string)$enrollment->currency)) {
            throw new InvalidArgumentException('La devise reçue de Stripe ne correspond pas à l\'inscription.');
        }

        if ((int)($checkoutSession['amount_total'] ?? -1) !== $this->amountInMinorUnit((float)$enrollment->amount_paid, (string)$enrollment->currency)) {
            throw new InvalidArgumentException('Le montant reçu de Stripe ne correspond pas à l\'inscription.');
        }
    }

    private function amountInMinorUnit(float $amount, string $currency): int
    {
        $multiplier = in_array(mb_strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true) ? 1 : 100;

        return (int)round($amount * $multiplier, 0, PHP_ROUND_HALF_UP);
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $checkoutSession
     * @return array<string, string|null>
     */
    private function gatewayResponse(array $checkoutSession, ?string $eventId): array
    {
        return [
            'stripe_event_id' => $eventId,
            'checkout_session_id' => $this->stringValue($checkoutSession['id'] ?? null),
            'payment_intent_id' => $this->stringValue($checkoutSession['payment_intent'] ?? null),
            'payment_status' => $this->stringValue($checkoutSession['payment_status'] ?? null),
        ];
    }

    /**
     * @throws SignatureVerificationException|Throwable
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret());
        $checkoutSession = $event->data->object->toArray();

        match ($event->type) {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => $this->completeCheckout($checkoutSession, $event->id),
            'checkout.session.async_payment_failed', 'checkout.session.expired' => $this->failCheckout($checkoutSession, $event->id),
            default => null,
        };
    }

    private function webhookSecret(): string
    {
        $secret = config('services.stripe.webhook_secret');

        if (!is_string($secret) || $secret === '') {
            throw new LogicException('Le secret de webhook Stripe est absent de la configuration.');
        }

        return $secret;
    }

    /**
     * @param array<string, mixed> $checkoutSession
     *
     * @throws Throwable
     */
    private function failCheckout(array $checkoutSession, string $eventId): void
    {
        DB::transaction(function () use ($checkoutSession, $eventId): void {
            $enrollment = $this->enrollmentForCheckout($checkoutSession);

            if ($enrollment->payment_status === EnrollmentPaymentEnum::PAID) {
                return;
            }

            $this->ensureCheckoutMatchesEnrollment($checkoutSession, $enrollment);

            $enrollment->update([
                'payment_status' => EnrollmentPaymentEnum::FAILED,
                'payment_gateway_response' => $this->gatewayResponse($checkoutSession, $eventId),
            ]);
        }, attempts: 3);
    }

    /**
     * @throws ApiErrorException
     * @throws Throwable
     */
    public function synchronizeCheckoutSession(Enrollment $enrollment, string $checkoutSessionId): void
    {
        if ($enrollment->stripe_checkout_session_id !== $checkoutSessionId) {
            throw new InvalidArgumentException('La session Stripe ne correspond pas à cette inscription.');
        }

        $checkoutSession = $this->stripeClient()
            ->checkout
            ->sessions
            ->retrieve($checkoutSessionId)
            ->toArray();

        if ((string)($checkoutSession['id'] ?? '') !== $checkoutSessionId) {
            throw new InvalidArgumentException('Stripe n\'a pas renvoyé la session de paiement attendue.');
        }

        $metadata = $checkoutSession['metadata'] ?? [];

        if (!is_array($metadata) || (string)($metadata['enrollment_id'] ?? '') !== (string)$enrollment->id) {
            throw new InvalidArgumentException('La session Stripe ne référence pas cette inscription.');
        }

        $this->completeCheckout($checkoutSession);
    }
}
