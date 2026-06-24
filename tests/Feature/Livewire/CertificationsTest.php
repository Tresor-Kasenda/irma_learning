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

it('renders active formations without price', function () {
    Formation::factory()->create([
        'is_active' => true,
        'price' => null,
    ]);

    $this->get(route('certifications'))
        ->assertStatus(200);
});

it('exposes the real content composition for each formation', function () {
    $formation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Gestion des risques',
    ]);
    $section = Section::factory()->for($formation)->create();

    Chapter::factory()->for($section)->create(['content_type' => 'video']);
    Chapter::factory()->for($section)->create(['content_type' => 'pdf']);
    Chapter::factory()->for($section)->count(2)->create(['content_type' => 'text']);

    $this->get(route('certifications'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Certifications/Index')
            ->where('formations.data.0.title', 'Gestion des risques')
            ->where('formations.data.0.chapter_count', 4)
            ->where('formations.data.0.video_count', 1)
            ->where('formations.data.0.pdf_count', 1)
            ->where('formations.data.0.text_count', 2)
            ->where('catalogStats.formations', 1)
            ->where('catalogStats.videos', 1)
            ->where('catalogStats.pdfs', 1)
            ->where('catalogStats.texts', 2)
            ->etc());
});

it('filters formations by content format', function () {
    $videoFormation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Formation vidéo',
    ]);
    $videoSection = Section::factory()->for($videoFormation)->create();
    Chapter::factory()->for($videoSection)->create(['content_type' => 'video']);

    $pdfFormation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Formation PDF',
    ]);
    $pdfSection = Section::factory()->for($pdfFormation)->create();
    Chapter::factory()->for($pdfSection)->create(['content_type' => 'pdf']);

    $this->get(route('certifications', ['content' => 'pdf']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.total', 1)
            ->where('formations.data.0.title', 'Formation PDF')
            ->where('filters.content', 'pdf')
            ->etc());
});

it('provides the current formation for an authenticated learner', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Audit et conformité',
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 42,
    ]);

    $this->actingAs($user)
        ->get(route('certifications'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('continueLearning.formation.title', 'Audit et conformité')
            ->where('continueLearning.progress_percentage', '42.00')
            ->etc());
});
