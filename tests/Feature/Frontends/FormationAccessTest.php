<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\FormationAccessCode;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $formation = Formation::factory()->create();

    $this->get(route('student.formations.validate-code', $formation->id))
        ->assertRedirect(route('login'));
});

test('the access code page renders the inertia component', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['title' => 'Cybersécurité']);

    $this->actingAs($user)
        ->get(route('student.formations.validate-code', $formation->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Frontends/ValidateAccess')
            ->where('formation.id', $formation->id)
            ->where('formation.title', 'Cybersécurité')
            ->etc());
});

test('a valid access code enrolls the learner and consumes the code', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $code = FormationAccessCode::factory()->for($formation)->create([
        'code' => 'WELCOME-2026',
        'is_used' => false,
    ]);

    $this->actingAs($user)
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => 'WELCOME-2026',
        ])
        ->assertRedirect(route('formation.show', $formation))
        ->assertSessionHas('success');

    expect($code->fresh())
        ->is_used->toBeTrue()
        ->user_id->toBe($user->id)
        ->and($code->fresh()->used_at)->not->toBeNull();

    $enrollment = Enrollment::where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toBe(EnrollmentStatusEnum::ACTIVE)
        ->and($enrollment->payment_status)->toBe(EnrollmentPaymentEnum::PAID);
});

test('access codes are normalized before validation', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    FormationAccessCode::factory()->for($formation)->create([
        'code' => 'IRMA-PRO-2026',
        'is_used' => false,
    ]);

    $this->actingAs($user)
        ->post(route('student.formations.validate-code', $formation), [
            'code' => '  irma-pro-2026  ',
        ])
        ->assertRedirect(route('formation.show', $formation));

    expect(Enrollment::query()
        ->where('user_id', $user->id)
        ->where('formation_id', $formation->id)
        ->exists())->toBeTrue();
});

test('repeated invalid access codes are rate limited', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->actingAs($user)
            ->post(route('student.formations.validate-code', $formation), [
                'code' => 'INVALID-'.$attempt,
            ]);
    }

    $this->actingAs($user)
        ->post(route('student.formations.validate-code', $formation), [
            'code' => 'INVALID-6',
        ])
        ->assertTooManyRequests();

    expect(Enrollment::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('an invalid code is rejected without enrolling', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    FormationAccessCode::factory()->for($formation)->create([
        'code' => 'WELCOME-2026',
        'is_used' => false,
    ]);

    $this->actingAs($user)
        ->from(route('student.formations.validate-code', $formation->id))
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => 'WRONG-CODE',
        ])
        ->assertRedirect(route('student.formations.validate-code', $formation->id))
        ->assertSessionHasErrors('code');

    expect(Enrollment::where('user_id', $user->id)->exists())->toBeFalse();
});

test('an already used code cannot be reused', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    FormationAccessCode::factory()->for($formation)->create([
        'code' => 'USED-1234',
        'is_used' => true,
    ]);

    $this->actingAs($user)
        ->from(route('student.formations.validate-code', $formation->id))
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => 'USED-1234',
        ])
        ->assertSessionHasErrors('code');

    expect(Enrollment::where('user_id', $user->id)->exists())->toBeFalse();
});

test('an expired code is rejected', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    FormationAccessCode::factory()->for($formation)->create([
        'code' => 'EXPIRED-99',
        'is_used' => false,
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->from(route('student.formations.validate-code', $formation->id))
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => 'EXPIRED-99',
        ])
        ->assertSessionHasErrors('code');

    expect(Enrollment::where('user_id', $user->id)->exists())->toBeFalse();
});

test('an already enrolled learner is redirected without consuming a code', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    Enrollment::factory()->for($user)->for($formation)->create();
    $code = FormationAccessCode::factory()->for($formation)->create([
        'code' => 'SPARE-CODE',
        'is_used' => false,
    ]);

    $this->actingAs($user)
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => 'SPARE-CODE',
        ])
        ->assertRedirect(route('formation.show', $formation));

    expect($code->fresh()->is_used)->toBeFalse();
});

test('the code field is required', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();

    $this->actingAs($user)
        ->from(route('student.formations.validate-code', $formation->id))
        ->post(route('student.formations.validate-code', $formation->id), [
            'code' => '',
        ])
        ->assertSessionHasErrors('code');
});
