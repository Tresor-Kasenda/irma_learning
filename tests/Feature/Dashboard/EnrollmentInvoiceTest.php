<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\UserRoleEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;

test('the enrollment invoice is restricted to the owner and administrators', function () {
    $owner = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $otherStudent = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create();

    $enrollment = Enrollment::factory()
        ->for($owner)
        ->for($formation)
        ->create([
            'payment_status' => EnrollmentPaymentEnum::PAID->value,
            'payment_processed_at' => now(),
            'payment_transaction_id' => 'TX-2026-0001',
            'amount_paid' => 150000,
            'currency' => 'XAF',
        ]);

    $this->actingAs($owner)
        ->get(route('enrollments.invoice', $enrollment))
        ->assertSuccessful();

    $this->actingAs($admin)
        ->get(route('enrollments.invoice', $enrollment))
        ->assertSuccessful();

    $this->actingAs($otherStudent)
        ->get(route('enrollments.invoice', $enrollment))
        ->assertForbidden();
});

test('the invoice cannot be accessed before payment confirmation', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $formation = Formation::factory()->create();

    $enrollment = Enrollment::factory()
        ->for($student)
        ->for($formation)
        ->create([
            'payment_status' => EnrollmentPaymentEnum::PENDING->value,
            'payment_processed_at' => null,
        ]);

    $this->actingAs($student)
        ->get(route('enrollments.invoice', $enrollment))
        ->assertNotFound();
});
