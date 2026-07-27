<?php

declare(strict_types=1);

use App\Contracts\MobileMoneyGateway;
use App\Data\MobileMoneyTransaction;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\ApplicationSetting;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    config()->set('app.url', 'https://learning.test');
    URL::forceRootUrl('https://learning.test');
    URL::forceScheme('https');
    setApplicationCurrency('CDF');
});

afterEach(function (): void {
    Cache::forget('application_settings');
    URL::forceRootUrl(null);
    URL::forceScheme(null);
});

test('the payment page offers Mobile Money when Shwary is configured for the course currency', function () {
    fakeMobileMoneyGateway(mobileMoneyTransaction(status: 'pending'));
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 5000]);

    $this->actingAs($user)
        ->get(route('student.payment.create', $formation))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Payment')
            ->where('mobileMoney.available', true)
            ->where('mobileMoney.currency', 'CDF')
            ->where('mobileMoney.countries.0.value', 'DRC')
            ->where('mobileMoney.countries.0.dial_code', '+243'));
});

test('a Mobile Money payment creates a pending Shwary enrollment and sends the confirmation request', function () {
    $gateway = fakeMobileMoneyGateway(mobileMoneyTransaction(status: 'pending'));
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 5000]);

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation), [
            'payment_method' => 'mobile_money',
            'mobile_money_country' => 'DRC',
            'mobile_money_phone' => '+243812345678',
        ])
        ->assertRedirect(route('student.payment.create', $formation));

    $enrollment = Enrollment::query()
        ->where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->firstOrFail();

    expect($enrollment)
        ->status->toBe(EnrollmentStatusEnum::ACTIVE)
        ->payment_status->toBe(EnrollmentPaymentEnum::PENDING)
        ->payment_method->toBe('mobile_money')
        ->payment_gateway->toBe('shwary')
        ->payment_transaction_id->toBe('shwary_tx_123')
        ->currency->toBe('CDF')
        ->and((float) $enrollment->amount_paid)->toBe(5000.0)
        ->and($enrollment->payment_gateway_response)->toMatchArray([
            'shwary_transaction_id' => 'shwary_tx_123',
            'reference_id' => 'shwary_ref_123',
            'country' => 'DRC',
            'recipient_phone_number' => '+243812345678',
            'status' => 'pending',
            'received_via' => 'initiation',
        ]);

    expect($gateway->initiations)->toHaveCount(1)
        ->and($gateway->initiations[0])->toMatchArray([
            'amount' => 5000,
            'phone_number' => '+243812345678',
            'country' => 'DRC',
        ])
        ->and($gateway->initiations[0]['callback_url'])->toStartWith('https://learning.test/shwary/webhook/');
});

test('a completed Shwary webhook grants course access only once', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 5000]);
    $enrollment = Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::PENDING,
        'payment_method' => 'mobile_money',
        'payment_gateway' => 'shwary',
        'payment_transaction_id' => 'shwary_tx_123',
        'amount_paid' => 5000,
        'currency' => 'CDF',
        'payment_gateway_response' => [
            'country' => 'DRC',
            'reference_id' => 'shwary_ref_123',
            'recipient_phone_number' => '+243812345678',
        ],
    ]);
    fakeMobileMoneyGateway(
        mobileMoneyTransaction(status: 'pending'),
        mobileMoneyTransaction(status: 'completed'),
    );
    $token = hash_hmac('sha256', 'shwary-webhook', (string) config('app.key'));

    $this->call('POST', route('shwary.webhook', ['token' => $token]), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{"id":"shwary_tx_123"}')
        ->assertNoContent();

    expect($enrollment->refresh())
        ->payment_status->toBe(EnrollmentPaymentEnum::PAID)
        ->payment_transaction_id->toBe('shwary_tx_123')
        ->payment_processed_at->not->toBeNull()
        ->and($enrollment->payment_gateway_response)->toMatchArray([
            'shwary_transaction_id' => 'shwary_tx_123',
            'reference_id' => 'shwary_ref_123',
            'status' => 'completed',
            'received_via' => 'webhook',
        ]);

    expect($user->notifications()
        ->where('type', 'learning.payment-confirmed')
        ->count())->toBe(1);

    $this->call('POST', route('shwary.webhook', ['token' => $token]), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{"id":"shwary_tx_123"}')
        ->assertNoContent();

    expect($user->notifications()
        ->where('type', 'learning.payment-confirmed')
        ->count())->toBe(1);
});

test('a Shwary webhook with an invalid endpoint token is rejected', function () {
    fakeMobileMoneyGateway(mobileMoneyTransaction(status: 'completed'));

    $this->call('POST', route('shwary.webhook', ['token' => str_repeat('a', 64)]), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{"id":"shwary_tx_123"}')
        ->assertNotFound();
});

test('Mobile Money requires the course currency supported by the selected country', function () {
    $gateway = fakeMobileMoneyGateway(mobileMoneyTransaction(status: 'pending'));
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 5000]);
    setApplicationCurrency('USD');

    $this->actingAs($user)
        ->from(route('student.payment.create', $formation))
        ->post(route('student.payment.create', $formation), [
            'payment_method' => 'mobile_money',
            'mobile_money_country' => 'DRC',
            'mobile_money_phone' => '+243812345678',
        ])
        ->assertSessionHasErrors('payment_method');

    expect($gateway->initiations)->toBeEmpty()
        ->and(Enrollment::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

function setApplicationCurrency(string $currency): void
{
    Cache::forget('application_settings');
    ApplicationSetting::query()->firstOrCreate()->update(['default_currency' => $currency]);
    Cache::forget('application_settings');
}

function mobileMoneyTransaction(string $status): MobileMoneyTransaction
{
    return new MobileMoneyTransaction(
        id: 'shwary_tx_123',
        referenceId: 'shwary_ref_123',
        amount: 5000,
        currency: 'CDF',
        status: $status,
        phoneNumber: '+243812345678',
    );
}

function fakeMobileMoneyGateway(
    MobileMoneyTransaction $initiationTransaction,
    ?MobileMoneyTransaction $webhookTransaction = null,
): MobileMoneyGateway {
    $gateway = new class($initiationTransaction, $webhookTransaction) implements MobileMoneyGateway
    {
        /**
         * @var array<int, array{amount: int, phone_number: string, country: string, callback_url: string}>
         */
        public array $initiations = [];

        public function __construct(
            private readonly MobileMoneyTransaction $initiationTransaction,
            private readonly ?MobileMoneyTransaction $webhookTransaction,
        ) {}

        public function isConfigured(): bool
        {
            return true;
        }

        public function initiate(
            int $amount,
            string $phoneNumber,
            App\Enums\MobileMoneyCountryEnum $country,
            string $callbackUrl,
        ): MobileMoneyTransaction {
            $this->initiations[] = [
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'country' => $country->value,
                'callback_url' => $callbackUrl,
            ];

            return $this->initiationTransaction;
        }

        public function parseWebhook(string $payload): MobileMoneyTransaction
        {
            return $this->webhookTransaction ?? $this->initiationTransaction;
        }
    };

    app()->instance(MobileMoneyGateway::class, $gateway);

    return $gateway;
}
