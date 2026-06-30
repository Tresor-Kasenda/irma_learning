<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard loads successfully for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard displays user statistics', function () {
    $user = User::factory()->create();
    $formations = Formation::factory()->count(3)->create(['is_active' => true]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[0]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[1]->id,
        'status' => EnrollmentStatusEnum::COMPLETED,
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[2]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard shows enrolled formations', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Test Formation',
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 50,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard provides reusable formation card data', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Formation multimédia',
    ]);
    $section = Section::factory()->for($formation)->create();
    Chapter::factory()->for($section)->create(['content_type' => 'video']);
    Chapter::factory()->for($section)->create(['content_type' => 'pdf']);
    Chapter::factory()->for($section)->create(['content_type' => 'text']);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 25,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->where('myEnrollments.0.formation.title', 'Formation multimédia')
            ->where('myEnrollments.0.formation.video_count', 1)
            ->where('myEnrollments.0.formation.pdf_count', 1)
            ->where('myEnrollments.0.formation.text_count', 1)
            ->where('catalogStats.formations', 1)
            ->etc());
});

test('dashboard shows available formations', function () {
    $user = User::factory()->create();
    Formation::factory()->create([
        'is_active' => true,
        'title' => 'Available Formation',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard renders correctly for authenticated user', function () {
    $user = User::factory()->create();
    $formations = Formation::factory()->count(2)->create(['is_active' => true]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[0]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});
