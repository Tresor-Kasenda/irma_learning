<?php

declare(strict_types=1);

use App\Models\Formation;
use App\Models\User;
use App\Services\EnrollmentService;

beforeEach(function () {
    $this->service = new EnrollmentService();
    $this->user = User::factory()->create();
});

test('it can create a free enrollment successfully', function () {
    $formation = Formation::factory()->create(['price' => 0]);

    $result = $this->service->createFreeEnrollment($this->user, $formation);

    expect($result['success'])->toBeTrue()
        ->and($result)->toHaveKey('enrollment')
        ->and($result['enrollment']->user_id)->toBe($this->user->id)
        ->and($result['enrollment']->formation_id)->toBe($formation->id)
        ->and($result['enrollment']->status->value)->toBe('active')
        ->and($result['enrollment']->payment_status->value)->toBe('free')
        ->and((float) $result['enrollment']->progress_percentage)->toBe(0.0);

    $this->assertDatabaseHas('enrollments', [
        'user_id' => $this->user->id,
        'formation_id' => $formation->id,
        'status' => 'active',
        'payment_status' => 'free',
    ]);
});

test('it prevents duplicate free enrollments', function () {
    $formation = Formation::factory()->create(['price' => 0]);

    // First enrollment
    $result1 = $this->service->createFreeEnrollment($this->user, $formation);
    expect($result1['success'])->toBeTrue();

    // Second enrollment attempt
    $result2 = $this->service->createFreeEnrollment($this->user, $formation);
    expect($result2['success'])->toBeFalse()
        ->and($result2['code'])->toBe('ALREADY_ENROLLED');
});

test('it rolls back on enrollment error', function () {
    $formation = Formation::factory()->create(['price' => 0]);

    // Force an error by using an invalid user
    $invalidUser = new User();
    $invalidUser->id = 99999;

    $result = $this->service->createFreeEnrollment($invalidUser, $formation);

    expect($result['success'])->toBeFalse()
        ->and($result['code'])->toBe('ENROLLMENT_ERROR');

    $this->assertDatabaseMissing('enrollments', [
        'user_id' => 99999,
        'formation_id' => $formation->id,
    ]);
});
