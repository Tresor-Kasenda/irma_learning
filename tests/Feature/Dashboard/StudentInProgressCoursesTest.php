<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function enrollLearner(
    User $user,
    Formation $formation,
    EnrollmentStatusEnum $status = EnrollmentStatusEnum::ACTIVE,
    EnrollmentPaymentEnum $paymentStatus = EnrollmentPaymentEnum::PAID,
    float $progress = 0,
): Enrollment {
    return Enrollment::factory()->for($user)->for($formation)->create([
        'status' => $status,
        'payment_status' => $paymentStatus,
        'progress_percentage' => $progress,
    ]);
}

test('guests are redirected to login', function () {
    $this->get(route('student.progress'))
        ->assertRedirect(route('login'));
});

test('page exposes the in-progress inertia contract with stats', function () {
    $user = User::factory()->create();
    $started = Formation::factory()->create(['is_active' => true]);
    $fresh = Formation::factory()->create(['is_active' => true]);
    $completed = Formation::factory()->create(['is_active' => true]);

    enrollLearner($user, $started, progress: 40);
    enrollLearner($user, $fresh, progress: 0);
    enrollLearner($user, $completed, EnrollmentStatusEnum::COMPLETED, progress: 100);

    $this->actingAs($user)
        ->get(route('student.progress'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Learnings/Index')
            ->where('filters.sort', 'recent')
            ->where('stats.inProgress', 2)
            ->where('stats.completed', 1)
            ->where('stats.averageProgress', 20)
            ->has('courses', 2)
            ->has('catalogStats.formations')
            ->has('catalogStats.videos')
            ->has('catalogStats.pdfs')
            ->has('catalogStats.texts')
            ->etc());
});

test('only accessible active and unfinished courses are listed', function () {
    $user = User::factory()->create();
    $inProgress = Formation::factory()->create(['is_active' => true]);
    $completed = Formation::factory()->create(['is_active' => true]);
    $unpaid = Formation::factory()->create(['is_active' => true]);
    $suspended = Formation::factory()->create(['is_active' => true]);
    $inactiveFormation = Formation::factory()->create(['is_active' => false]);

    enrollLearner($user, $inProgress, progress: 55);
    enrollLearner($user, $completed, progress: 100);
    enrollLearner($user, $unpaid, paymentStatus: EnrollmentPaymentEnum::PENDING, progress: 30);
    enrollLearner($user, $suspended, EnrollmentStatusEnum::SUSPENDED, progress: 30);
    enrollLearner($user, $inactiveFormation, progress: 30);

    $this->actingAs($user)
        ->get(route('student.progress'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.inProgress', 1)
            ->where('courses.0.id', $inProgress->id)
            ->etc());
});

test('courses are sorted by progression when requested', function () {
    $user = User::factory()->create();
    $low = Formation::factory()->create(['is_active' => true]);
    $high = Formation::factory()->create(['is_active' => true]);

    enrollLearner($user, $low, progress: 20);
    enrollLearner($user, $high, progress: 80);

    $this->actingAs($user)
        ->get(route('student.progress', ['sort' => 'progress-desc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'progress-desc')
            ->where('courses.0.id', $high->id)
            ->where('courses.1.id', $low->id)
            ->etc());

    $this->actingAs($user)
        ->get(route('student.progress', ['sort' => 'progress-asc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('courses.0.id', $low->id)
            ->where('courses.1.id', $high->id)
            ->etc());
});

test('invalid sort falls back to recent activity', function () {
    $user = User::factory()->create();
    enrollLearner($user, Formation::factory()->create(['is_active' => true]), progress: 10);

    $this->actingAs($user)
        ->get(route('student.progress', ['sort' => 'random']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'recent')
            ->etc());
});
