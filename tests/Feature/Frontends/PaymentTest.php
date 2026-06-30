<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

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
            ->component('Frontends/Payment')
            ->where('formation.id', $formation->id)
            ->where('formation.title', 'DevOps')
            ->where('formation.price', '75.00')
            ->etc());
});

test('a mobile money payment enrols the learner', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'mobile_money',
            'operator' => 'orange',
            'phone' => '+243 812 345 678',
        ])
        ->assertRedirect(route('course.player', $formation->id))
        ->assertSessionHas('success');

    $enrollment = Enrollment::where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toBe(EnrollmentStatusEnum::ACTIVE)
        ->and($enrollment->payment_status)->toBe(EnrollmentPaymentEnum::PAID)
        ->and($enrollment->payment_method)->toBe('mobile_money')
        ->and($enrollment->payment_gateway)->toBe('mobile_money')
        ->and((float) $enrollment->amount_paid)->toBe(50.0);
});

test('a card payment is recorded against the stripe gateway', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 120]);

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertRedirect(route('course.player', $formation->id));

    expect(Enrollment::where('user_id', $user->id)->first())
        ->payment_method->toBe('card')
        ->payment_gateway->toBe('stripe');
});

test('mobile money requires a phone number', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);

    $this->actingAs($user)
        ->from(route('student.payment.create', $formation->id))
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'mobile_money',
            'phone' => '',
        ])
        ->assertSessionHasErrors('phone');

    expect(Enrollment::where('user_id', $user->id)->exists())->toBeFalse();
});

test('the payment method must be supported', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);

    $this->actingAs($user)
        ->from(route('student.payment.create', $formation->id))
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'paypal',
        ])
        ->assertSessionHasErrors('payment_method');
});

test('an already enrolled learner is redirected without paying twice', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['price' => 50]);
    Enrollment::factory()->for($user)->for($formation)->create();

    $this->actingAs($user)
        ->post(route('student.payment.create', $formation->id), [
            'payment_method' => 'card',
        ])
        ->assertRedirect(route('course.player', $formation->id));

    expect(Enrollment::where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->count())->toBe(1);
});
