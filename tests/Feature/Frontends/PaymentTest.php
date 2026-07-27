<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

beforeEach(function (): void {
    config()->set('services.stripe.secret', 'sk_test_example');
    config()->set('services.stripe.webhook_secret', 'whsec_test_example');

    $this->previousStripeHttpClient = ApiRequestor::httpClient();
});

afterEach(function (): void {
    ApiRequestor::setHttpClient($this->previousStripeHttpClient);
});

test('guests are redirected to login', function () {
    $formation = Formation::factory()->create(['price' => 50]);

    $this->get(route('student.payment.create', $formation->id))
        ->assertRedirect(route('login'));
});

test('the payment page renders with the formation summary', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['title' => 'DevOps', 'price' => 75]);

    $this->actingAs($user)
        ->get(route('student.payment.create', $formation->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Payment')
            ->where('formation.id', $formation->id)
            ->where('formation.title', 'DevOps')
            ->where('formation.price', '75.00')
            ->etc());
});

test('a card payment creates a pending enrollment and redirects to Stripe Checkout', function () {
    $user = User::factory()->create(['email' => 'learner@example.com']);
    $formation = Formation::factory()->create(['title' => 'DevOps', 'price' => 120]);
    $stripeHttpClient = fakeStripeHttpClient();
    ApiRequestor::setHttpClient($stripeHttpClient);

    $this->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertConflict()
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.com/c/pay/cs_test_123');

    $enrollment = Enrollment::query()
        ->where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->firstOrFail();

    expect($enrollment)
        ->status->toBe(EnrollmentStatusEnum::ACTIVE)
        ->payment_status->toBe(EnrollmentPaymentEnum::PENDING)
        ->payment_method->toBe('card')
        ->payment_gateway->toBe('stripe')
        ->stripe_checkout_session_id->toBe('cs_test_123')
        ->currency->toBe('USD')
        ->payment_processed_at->toBeNull()
        ->and((float) $enrollment->amount_paid)->toBe(120.0);

    expect($stripeHttpClient->requests)->toHaveCount(1)
        ->and($stripeHttpClient->requests[0]['params']['customer_email'])->toBe('learner@example.com')
        ->and($stripeHttpClient->requests[0]['params']['line_items'][0]['price_data']['unit_amount'])->toBe(12000)
        ->and($stripeHttpClient->requests[0]['params']['metadata']['enrollment_id'])->toBe((string) $enrollment->id);

    expect($stripeHttpClient->requests[0]['params'])->not->toHaveKey('payment_method_types');
});

test('an open Stripe Checkout session is reused for a pending enrollment', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
    $stripeHttpClient = fakeStripeHttpClient();
    ApiRequestor::setHttpClient($stripeHttpClient);

    $this->actingAs($user)
        ->withHeader('X-Inertia', 'true')
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertConflict()
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.com/c/pay/cs_test_123');

    expect($stripeHttpClient->requests)
        ->toHaveCount(1)
        ->and($stripeHttpClient->requests[0]['method'])->toBe('get');
});

test('a valid Stripe webhook confirms the enrollment only once', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
    $payload = stripeWebhookPayload($enrollment);
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_example');

    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], $payload)
        ->assertNoContent();

    expect($enrollment->refresh())
        ->payment_status->toBe(EnrollmentPaymentEnum::PAID)
        ->status->toBe(EnrollmentStatusEnum::ACTIVE)
        ->payment_transaction_id->toBe('pi_test_123')
        ->payment_processed_at->not->toBeNull()
        ->and($enrollment->payment_gateway_response)->toMatchArray([
            'stripe_event_id' => 'evt_test_123',
            'checkout_session_id' => 'cs_test_123',
            'payment_intent_id' => 'pi_test_123',
            'payment_status' => 'paid',
        ]);

    expect($user->notifications()
        ->where('type', 'learning.payment-confirmed')
        ->count())->toBe(1);

    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], $payload)
        ->assertNoContent();

    expect(Enrollment::query()->whereKey($enrollment->id)->count())->toBe(1);
    expect($user->notifications()
        ->where('type', 'learning.payment-confirmed')
        ->count())->toBe(1);
});

test('the payment success return synchronizes a paid Stripe session', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
    ApiRequestor::setHttpClient(paidStripeHttpClient($enrollment));

    $this->actingAs($user)
        ->get(route('student.payment.success', [
            'formation' => $formation->id,
            'session_id' => 'cs_test_123',
        ]))
        ->assertRedirect(route('course.player', $formation->id));

    expect($enrollment->refresh())
        ->payment_status->toBe(EnrollmentPaymentEnum::PAID)
        ->payment_transaction_id->toBe('pi_test_123')
        ->payment_processed_at->not->toBeNull()
        ->and($enrollment->payment_gateway_response)->toMatchArray([
            'checkout_session_id' => 'cs_test_123',
            'payment_intent_id' => 'pi_test_123',
            'payment_status' => 'paid',
        ]);
});

test('retrying payment synchronizes a completed Stripe session', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
    ApiRequestor::setHttpClient(paidStripeHttpClient($enrollment));

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertRedirect(route('course.player', $formation->id));

    expect($enrollment->refresh()->payment_status)->toBe(EnrollmentPaymentEnum::PAID);
});

test('the payment success return does not accept another Stripe session', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);

    $this->actingAs($user)
        ->get(route('student.payment.success', [
            'formation' => $formation->id,
            'session_id' => 'cs_test_other',
        ]))
        ->assertRedirect(route('student.payment.create', $formation->id));

    expect($enrollment->refresh()->payment_status)->toBe(EnrollmentPaymentEnum::PENDING);
});

test('a failed Stripe webhook keeps the course locked', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'card',
        'payment_gateway' => 'stripe',
        'amount_paid' => 120,
        'currency' => 'USD',
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
    $payload = stripeWebhookPayload($enrollment, 'checkout.session.async_payment_failed', 'unpaid');
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_example');

    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], $payload)
        ->assertNoContent();

    expect($enrollment->refresh())
        ->payment_status->toBe(EnrollmentPaymentEnum::FAILED)
        ->payment_processed_at->toBeNull();
});

test('a Stripe webhook with an invalid signature is rejected', function () {
    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=invalid',
    ], '{"object":"event"}')
        ->assertBadRequest();
});

test('a Mobile Money payment requires its country and phone number', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);

    $this->actingAs($user)
        ->from(route('student.payment.create', $formation->id))
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'mobile_money',
        ])
        ->assertSessionHasErrors(['mobile_money_country', 'mobile_money_phone']);

    expect(Enrollment::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('an already paid learner is not sent to Stripe again', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::PAID,
    ]);

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertRedirect(route('course.player', $formation->id));

    expect(Enrollment::query()
        ->where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->count())->toBe(1);
});

function fakeStripeHttpClient(): ClientInterface
{
    return new class implements ClientInterface
    {
        /**
         * @var array<int, array{method: string, url: string, params: array<mixed>}>
         */
        public array $requests = [];

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            $this->requests[] = [
                'method' => $method,
                'url' => $absUrl,
                'params' => $params,
            ];

            return [json_encode([
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
                'status' => 'open',
            ], JSON_THROW_ON_ERROR), 200, []];
        }
    };
}

function paidStripeHttpClient(Enrollment $enrollment): ClientInterface
{
    return new class($enrollment) implements ClientInterface
    {
        public function __construct(private Enrollment $enrollment) {}

        public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
        {
            return [json_encode([
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
                'status' => 'complete',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_test_123',
                'currency' => 'usd',
                'amount_total' => 12000,
                'metadata' => [
                    'enrollment_id' => (string) $this->enrollment->id,
                    'formation_id' => (string) $this->enrollment->formation_id,
                ],
            ], JSON_THROW_ON_ERROR), 200, []];
        }
    };
}

function stripeWebhookPayload(
    Enrollment $enrollment,
    string $type = 'checkout.session.completed',
    string $paymentStatus = 'paid',
): string {
    return json_encode([
        'id' => 'evt_test_123',
        'object' => 'event',
        'type' => $type,
        'data' => [
            'object' => [
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
                'payment_status' => $paymentStatus,
                'payment_intent' => 'pi_test_123',
                'currency' => 'usd',
                'amount_total' => 12000,
                'metadata' => [
                    'enrollment_id' => (string) $enrollment->id,
                    'formation_id' => (string) $enrollment->formation_id,
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);
}
