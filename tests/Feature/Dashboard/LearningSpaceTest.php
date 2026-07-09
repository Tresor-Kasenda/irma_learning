<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
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
            ->where('recentFormations.total', 2)
            ->where("completedCertificates.{$completedFormation->id}.id", $certificate->id)
            ->etc());
});

test('dashboard paginates recent formations by nine', function () {
    $user = User::factory()->create();
    Formation::factory()->count(10)->create(['is_active' => true]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Index')
            ->where('recentFormations.total', 10)
            ->where('recentFormations.per_page', 9)
            ->where('recentFormations.last_page', 2)
            ->has('recentFormations.data', 9)
            ->etc());
});

test('dashboard resumes the last opened chapter across active formations', function () {
    $user = User::factory()->create();
    $olderFormation = Formation::factory()->create(['is_active' => true]);
    $latestFormation = Formation::factory()->create(['is_active' => true]);
    $olderSection = Section::factory()->for($olderFormation)->create();
    $latestSection = Section::factory()->for($latestFormation)->create();
    $olderChapter = Chapter::factory()->for($olderSection)->create(['is_active' => true]);
    $latestChapter = Chapter::factory()->for($latestSection)->create(['is_active' => true]);

    Enrollment::factory()->for($user)->for($olderFormation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);
    Enrollment::factory()->for($user)->for($latestFormation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);

    UserProgress::factory()->for($user)->create([
        'trackable_type' => Chapter::class,
        'trackable_id' => $olderChapter->id,
        'status' => UserProgressEnum::IN_PROGRESS,
        'updated_at' => now()->subHour(),
    ]);
    UserProgress::factory()->for($user)->create([
        'trackable_type' => Chapter::class,
        'trackable_id' => $latestChapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Index')
            ->where('continueWatching.trackable.id', $latestChapter->id)
            ->where('continueWatching.trackable.section.formation.id', $latestFormation->id)
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

test('course detail exposes actual progress and the last opened chapter', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['is_active' => true]);
    $section = Section::factory()->for($formation)->create();
    $firstChapter = Chapter::factory()->for($section)->create(['is_active' => true, 'order_position' => 1]);
    $secondChapter = Chapter::factory()->for($section)->create(['is_active' => true, 'order_position' => 2]);
    Exam::factory()->forSection($section)->active()->create();

    Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 100,
    ]);

    UserProgress::factory()->for($user)->create([
        'trackable_type' => Chapter::class,
        'trackable_id' => $firstChapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'updated_at' => now()->subHour(),
    ]);
    UserProgress::factory()->for($user)->create([
        'trackable_type' => Chapter::class,
        'trackable_id' => $secondChapter->id,
        'status' => UserProgressEnum::IN_PROGRESS,
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('student.learnings.detail', $formation->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Learnings/CourseDetail')
            ->where('continueChapterId', $secondChapter->id)
            ->where('learningProgress', 33.33)
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
