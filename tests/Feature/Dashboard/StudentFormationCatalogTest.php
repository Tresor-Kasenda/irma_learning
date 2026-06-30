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

function enrollInFormation(
    User $user,
    Formation $formation,
    EnrollmentStatusEnum $status = EnrollmentStatusEnum::ACTIVE,
    EnrollmentPaymentEnum $paymentStatus = EnrollmentPaymentEnum::FREE,
    float $progress = 0,
): Enrollment {
    return Enrollment::factory()->for($user)->for($formation)->create([
        'status' => $status,
        'payment_status' => $paymentStatus,
        'progress_percentage' => $progress,
    ]);
}

test('guests are redirected to login', function () {
    $this->get(route('student.learnings'))
        ->assertRedirect(route('login'));
});

test('catalog exposes the expected inertia contract and global tab counts', function () {
    $user = User::factory()->create();
    $recent = Formation::factory()->create(['title' => 'Formation récente', 'is_active' => true]);
    $started = Formation::factory()->create(['title' => 'Formation commencée', 'is_active' => true]);
    $completed = Formation::factory()->create(['title' => 'Formation terminée', 'is_active' => true]);
    Formation::factory()->create(['title' => 'Formation à découvrir', 'is_active' => true]);

    enrollInFormation($user, $recent);
    enrollInFormation($user, $started, progress: 40);
    enrollInFormation($user, $completed, EnrollmentStatusEnum::COMPLETED, progress: 100);

    $this->actingAs($user)
        ->get(route('student.learnings'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Index')
            ->where('filters.tab', 'recent')
            ->where('filters.sort', 'last-interacted')
            ->where('formations.total', 3)
            ->where('tabCounts.recent', 3)
            ->where('tabCounts.discover', 1)
            ->where('tabCounts.started', 1)
            ->where('tabCounts.completed', 1)
            ->has('catalogStats.formations')
            ->has('catalogStats.videos')
            ->has('catalogStats.pdfs')
            ->has('catalogStats.texts')
            ->etc());
});

test('discover tab excludes every formation already associated with the learner', function () {
    $user = User::factory()->create();
    $available = Formation::factory()->create(['title' => 'Disponible', 'is_active' => true]);
    $enrolled = Formation::factory()->create(['title' => 'Déjà inscrite', 'is_active' => true]);
    $pending = Formation::factory()->create(['title' => 'Paiement en attente', 'is_active' => true]);
    Formation::factory()->create(['title' => 'Inactive', 'is_active' => false]);

    enrollInFormation($user, $enrolled);
    enrollInFormation(
        $user,
        $pending,
        paymentStatus: EnrollmentPaymentEnum::PENDING,
    );

    $this->actingAs($user)
        ->get(route('student.learnings', ['tab' => 'discover']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.tab', 'discover')
            ->where('filters.sort', 'popular')
            ->where('formations.total', 1)
            ->where('formations.data.0.id', $available->id)
            ->etc());
});

test('started and completed tabs apply payment and progress rules', function () {
    $user = User::factory()->create();
    $notStarted = Formation::factory()->create(['is_active' => true]);
    $started = Formation::factory()->create(['is_active' => true]);
    $completedByStatus = Formation::factory()->create(['is_active' => true]);
    $completedByProgress = Formation::factory()->create(['is_active' => true]);
    $unpaid = Formation::factory()->create(['is_active' => true]);

    enrollInFormation($user, $notStarted);
    enrollInFormation($user, $started, progress: 45);
    enrollInFormation($user, $completedByStatus, EnrollmentStatusEnum::COMPLETED, progress: 80);
    enrollInFormation($user, $completedByProgress, progress: 100);
    enrollInFormation($user, $unpaid, progress: 60, paymentStatus: EnrollmentPaymentEnum::PENDING);

    $this->actingAs($user)
        ->get(route('student.learnings', ['tab' => 'started']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.total', 1)
            ->where('formations.data.0.id', $started->id)
            ->etc());

    $this->actingAs($user)
        ->get(route('student.learnings', ['tab' => 'completed']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.total', 2)
            ->where('formations.data', fn ($formations) => collect($formations)
                ->pluck('id')
                ->sort()
                ->values()
                ->all() === collect([$completedByStatus->id, $completedByProgress->id])
                ->sort()
                ->values()
                ->all())
            ->etc());
});

test('catalog filters by search level and active chapter format', function () {
    $user = User::factory()->create();
    $matching = Formation::factory()->create([
        'title' => 'Audit vidéo',
        'description' => 'Programme conformité',
        'difficulty_level' => 'advanced',
        'is_active' => true,
    ]);
    $matchingSection = Section::factory()->for($matching)->create();
    Chapter::factory()->for($matchingSection)->create([
        'content_type' => 'video',
        'is_active' => true,
    ]);

    $other = Formation::factory()->create([
        'title' => 'Audit PDF',
        'description' => 'Programme conformité',
        'difficulty_level' => 'advanced',
        'is_active' => true,
    ]);
    $otherSection = Section::factory()->for($other)->create();
    Chapter::factory()->for($otherSection)->create([
        'content_type' => 'pdf',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('student.learnings', [
            'tab' => 'discover',
            'search' => 'Audit',
            'level' => 'advanced',
            'content' => 'video',
            'sort' => 'title',
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.total', 1)
            ->where('formations.data.0.id', $matching->id)
            ->where('filters.search', 'Audit')
            ->where('filters.level', 'advanced')
            ->where('filters.content', 'video')
            ->where('filters.sort', 'title')
            ->where('tabCounts.discover', 2)
            ->etc());
});

test('invalid filters are normalized and discover results are paginated by nine', function () {
    $user = User::factory()->create();
    Formation::factory()->count(10)->create(['is_active' => true]);

    $this->actingAs($user)
        ->get(route('student.learnings', [
            'tab' => 'invalid',
            'level' => 'expert',
            'content' => 'podcast',
            'sort' => 'random',
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.tab', 'recent')
            ->where('filters.level', '')
            ->where('filters.content', '')
            ->where('filters.sort', 'last-interacted')
            ->etc());

    $this->actingAs($user)
        ->get(route('student.learnings', ['tab' => 'discover']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.total', 10)
            ->where('formations.per_page', 9)
            ->has('formations.data', 9)
            ->where('formations.last_page', 2)
            ->etc());
});
