<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard exposes recent formations and completed certificates', function () {
    $user = User::factory()->create();

    Formation::factory()->create(['is_active' => true, 'title' => 'Nouvelle formation']);

    $completedFormation = Formation::factory()->create(['is_active' => true]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $completedFormation->id,
        'status' => EnrollmentStatusEnum::COMPLETED,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 100,
    ]);

    $certificate = Certificate::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $completedFormation->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Index')
            ->has('recentFormations', 2)
            ->where("completedCertificates.{$completedFormation->id}.id", $certificate->id)
            ->etc());
});

test('course detail returns formation, enrollment and certificate for the owner', function () {
    $user = User::factory()->create();

    $formation = Formation::factory()->create(['is_active' => true, 'title' => 'Formation à terminer']);
    $section = Section::factory()->for($formation)->create();
    Chapter::factory()->for($section)->create();

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => EnrollmentStatusEnum::COMPLETED,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 100,
    ]);

    $certificate = Certificate::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('student.learnings.detail', $formation->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Learnings/CourseDetail')
            ->where('formation.title', 'Formation à terminer')
            ->has('enrollment')
            ->where('certificate.id', $certificate->id)
            ->etc());
});

test('certificate page is visible to its owner', function () {
    $user = User::factory()->create();
    $certificate = Certificate::factory()->create(['user_id' => $user->id, 'status' => 'active']);

    $this->actingAs($user)
        ->get(route('certificats.show', $certificate))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Certifications/Show')
            ->where('certificate.id', $certificate->id)
            ->where('certificate.certificate_number', $certificate->certificate_number)
            ->etc());
});

test('certificate page is forbidden for another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $certificate = Certificate::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($intruder)
        ->get(route('certificats.show', $certificate))
        ->assertForbidden();
});

test('certificate page redirects guests to login', function () {
    $certificate = Certificate::factory()->create();

    $this->get(route('certificats.show', $certificate))
        ->assertRedirect(route('login'));
});
