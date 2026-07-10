<?php

declare(strict_types=1);

use App\Models\Exam;
use App\Models\Formation;
use App\Models\Question;
use App\Models\Section;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->section = Section::factory()
        ->for(Formation::factory())
        ->create();
});

// ── Auth ─────────────────────────────────────────────────────────────────────

test('guests are redirected to login for exams', function () {
    $this->get(route('admin.exams.index'))->assertRedirect(route('login'));
});

test('guests are redirected to login for attempts', function () {
    $this->get(route('admin.attempts.index'))->assertRedirect(route('login'));
});

test('a student cannot access the exams admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.exams.index'))
        ->assertForbidden();
});

test('a student cannot access the attempts admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.attempts.index'))
        ->assertForbidden();
});

// ── Index ────────────────────────────────────────────────────────────────────

test('the exam index lists exams', function () {
    Exam::factory()->for($this->section, 'examable')->create(['title' => 'Test Symfony']);

    $this->actingAs($this->admin)
        ->get(route('admin.exams.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Index')
            ->has('exams.data', 1)
            ->where('exams.data.0.title', 'Test Symfony')
            ->etc());
});

test('the exam index can filter by type', function () {
    $formation = Formation::factory()->create();
    Exam::factory()->for($this->section, 'examable')->create(['title' => 'Section Exam']);
    Exam::factory()->for($formation, 'examable')->create(['title' => 'Formation Exam']);

    $this->actingAs($this->admin)
        ->get(route('admin.exams.index', ['examable_type' => 'Section']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('exams.data', 1)
            ->where('exams.data.0.title', 'Section Exam')
            ->etc());
});

test('the exam index can filter by active status', function () {
    Exam::factory()->for($this->section, 'examable')->create(['title' => 'Actif', 'is_active' => true]);
    Exam::factory()->for($this->section, 'examable')->create(['title' => 'Inactif', 'is_active' => false]);

    $this->actingAs($this->admin)
        ->get(route('admin.exams.index', ['is_active' => '1']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('exams.data', 1)
            ->where('exams.data.0.title', 'Actif')
            ->etc());
});

// ── Create / Show / Edit ────────────────────────────────────────────────────

test('the create page provides parent options', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.exams.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Form')
            ->where('exam', null)
            ->has('parentOptions')
            ->etc());
});

test('the create page excludes sections and formations that already have an exam', function () {
    $availableSection = Section::factory()->for(Formation::factory())->create(['title' => 'Section disponible']);
    $usedFormation = Formation::factory()->create(['title' => 'Formation utilisée']);

    Exam::factory()->for($this->section, 'examable')->create();
    Exam::factory()->for($usedFormation, 'examable')->create();

    $this->actingAs($this->admin)
        ->get(route('admin.exams.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('parentOptions', fn ($options): bool => collect($options)
                ->pluck('value')
                ->contains('App\\Models\\Section:'.$availableSection->id)
                && ! collect($options)->pluck('value')->contains('App\\Models\\Section:'.$this->section->id)
                && ! collect($options)->pluck('value')->contains('App\\Models\\Formation:'.$usedFormation->id))
            ->etc());
});

test('the edit page keeps its current parent available and excludes other used parents', function () {
    $exam = Exam::factory()->for($this->section, 'examable')->create();
    $otherSection = Section::factory()->for(Formation::factory())->create();
    Exam::factory()->for($otherSection, 'examable')->create();

    $this->actingAs($this->admin)
        ->get(route('admin.exams.edit', $exam))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('parentOptions', fn ($options): bool => collect($options)
                ->pluck('value')
                ->contains('App\\Models\\Section:'.$this->section->id)
                && ! collect($options)->pluck('value')->contains('App\\Models\\Section:'.$otherSection->id))
            ->etc());
});

test('an admin can create an exam for a section', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.exams.store'), [
            'title' => 'Examen PHP',
            'description' => 'Test de connaissances PHP',
            'duration_minutes' => 60,
            'passing_score' => 70,
            'max_attempts' => 3,
            'examable_type' => 'App\Models\Section',
            'examable_id' => $this->section->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas(Exam::class, [
        'title' => 'Examen PHP',
        'examable_type' => 'App\Models\Section',
        'examable_id' => $this->section->id,
    ]);
});

test('an admin can view an exam detail page', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->has(Question::factory()->count(3))
        ->create(['title' => 'Examen Vue']);

    $this->actingAs($this->admin)
        ->get(route('admin.exams.show', $exam))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Show')
            ->where('exam.title', 'Examen Vue')
            ->where('exam.questions_count', 3)
            ->has('exam.questions', 3)
            ->etc());
});

test('an admin can edit an exam', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create(['title' => 'Original']);

    $this->actingAs($this->admin)
        ->get(route('admin.exams.edit', $exam))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Exams/Form')
            ->where('exam.title', 'Original')
            ->etc());
});

test('an admin can update an exam', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create(['title' => 'Original']);

    $this->actingAs($this->admin)
        ->post(route('admin.exams.update', $exam), [
            'title' => 'Modifié',
            'duration_minutes' => 45,
            'passing_score' => 80,
            'max_attempts' => 2,
            'examable_type' => 'App\Models\Section',
            'examable_id' => $this->section->id,
        ])
        ->assertRedirect(route('admin.exams.show', $exam));

    $this->assertDatabaseHas(Exam::class, [
        'id' => $exam->id,
        'title' => 'Modifié',
        'duration_minutes' => 45,
    ]);
});

test('an admin can toggle exam active state', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create(['is_active' => false]);

    $this->actingAs($this->admin)
        ->patch(route('admin.exams.toggle-active', $exam))
        ->assertRedirect();

    $this->assertDatabaseHas(Exam::class, ['id' => $exam->id, 'is_active' => true]);
});

test('an admin can duplicate an exam with questions and options', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->has(Question::factory()->hasOptions(3))
        ->create(['title' => 'Original']);

    $this->actingAs($this->admin)
        ->post(route('admin.exams.duplicate', $exam))
        ->assertRedirect();

    $this->assertDatabaseHas(Exam::class, ['title' => 'Original (copie)', 'is_active' => false]);
    $duplicate = Exam::where('title', 'Original (copie)')->first();
    expect($duplicate)->not->toBeNull();
    expect($duplicate->questions()->count())->toBe(1);
    expect($duplicate->questions()->first()->options()->count())->toBe(3);
});

test('an admin can delete an exam', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.exams.destroy', $exam))
        ->assertRedirect();

    $this->assertDatabaseMissing(Exam::class, ['id' => $exam->id]);
});

// ── Questions ────────────────────────────────────────────────────────────────

test('an admin can add a question to an exam', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create();

    $this->actingAs($this->admin)
        ->post(route('admin.exams.questions.store', $exam), [
            'question_text' => 'Quelle est la capitale de la France ?',
            'question_type' => 'single_choice',
            'points' => 2,
            'options' => [
                ['option_text' => 'Paris', 'is_correct' => true, 'order_position' => 1],
                ['option_text' => 'Lyon', 'is_correct' => false, 'order_position' => 2],
                ['option_text' => 'Marseille', 'is_correct' => false, 'order_position' => 3],
                ['option_text' => 'Lille', 'is_correct' => false, 'order_position' => 4],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas(Question::class, [
        'exam_id' => $exam->id,
        'question_text' => 'Quelle est la capitale de la France ?',
    ]);
});

test('a single_choice question must have exactly one correct answer', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create();

    $this->actingAs($this->admin)
        ->post(route('admin.exams.questions.store', $exam), [
            'question_text' => 'Test',
            'question_type' => 'single_choice',
            'points' => 1,
            'options' => [
                ['option_text' => 'A', 'is_correct' => false, 'order_position' => 1],
                ['option_text' => 'B', 'is_correct' => false, 'order_position' => 2],
                ['option_text' => 'C', 'is_correct' => false, 'order_position' => 3],
                ['option_text' => 'D', 'is_correct' => false, 'order_position' => 4],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('options');

    $this->assertDatabaseMissing(Question::class, ['question_text' => 'Test']);
});

test('a true false question accepts exactly two options', function () {
    $exam = Exam::factory()->for($this->section, 'examable')->create();

    $this->actingAs($this->admin)
        ->post(route('admin.exams.questions.store', $exam), [
            'question_text' => 'Laravel est un framework PHP ?',
            'question_type' => 'true_false',
            'points' => 1,
            'options' => [
                ['option_text' => 'Vrai', 'is_correct' => true, 'order_position' => 1],
                ['option_text' => 'Faux', 'is_correct' => false, 'order_position' => 2],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($exam->questions()->firstOrFail()->options()->count())->toBe(2);
});

test('a true false question rejects more than two options', function () {
    $exam = Exam::factory()->for($this->section, 'examable')->create();

    $this->actingAs($this->admin)
        ->post(route('admin.exams.questions.store', $exam), [
            'question_text' => 'Question invalide',
            'question_type' => 'true_false',
            'points' => 1,
            'options' => [
                ['option_text' => 'Vrai', 'is_correct' => true],
                ['option_text' => 'Faux', 'is_correct' => false],
                ['option_text' => 'Peut-être', 'is_correct' => false],
            ],
        ])
        ->assertSessionHasErrors('options');

    $this->assertDatabaseMissing(Question::class, ['question_text' => 'Question invalide']);
});

test('an admin can delete a question', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->has(Question::factory())
        ->create();
    $question = $exam->questions()->first();

    $this->actingAs($this->admin)
        ->delete(route('admin.exams.questions.destroy', [$exam, $question]))
        ->assertRedirect();

    $this->assertDatabaseMissing(Question::class, ['id' => $question->id]);
});

test('an admin can reorder questions', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create();
    $q1 = $exam->questions()->create(Question::factory()->make(['order_position' => 1])->toArray());
    $q2 = $exam->questions()->create(Question::factory()->make(['order_position' => 2])->toArray());
    $q3 = $exam->questions()->create(Question::factory()->make(['order_position' => 3])->toArray());

    $this->actingAs($this->admin)
        ->post(route('admin.exams.questions.reorder', $exam), [
            'questions' => [
                ['id' => $q3->id, 'order_position' => 1],
                ['id' => $q2->id, 'order_position' => 2],
                ['id' => $q1->id, 'order_position' => 3],
            ],
        ])
        ->assertRedirect();

    $this->assertEquals(1, $q3->fresh()->order_position);
    $this->assertEquals(3, $q1->fresh()->order_position);
});

// ── Section show includes exam ──────────────────────────────────────────────

test('the section detail page includes exam data when an exam exists', function () {
    $exam = Exam::factory()
        ->for($this->section, 'examable')
        ->create(['title' => 'Examen Section']);

    $this->actingAs($this->admin)
        ->get(route('admin.sections.show', $this->section))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('section.has_exam', true)
            ->where('section.exam.id', $exam->id)
            ->where('section.exam.title', 'Examen Section')
            ->etc());
});

test('the section detail page shows null exam when no exam exists', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.sections.show', $this->section))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('section.has_exam', false)
            ->where('section.exam', null)
            ->etc());
});
