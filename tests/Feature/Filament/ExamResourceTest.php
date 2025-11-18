<?php

declare(strict_types=1);

use App\Filament\Resources\ExamResource\Pages\CreateExam;
use App\Filament\Resources\ExamResource\Pages\ListExams;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin@test.com',
        'role' => 'admin',
    ]);

    $this->actingAs($this->admin);
});

test('admin can view exam list page', function () {
    Livewire::test(ListExams::class)
        ->assertSuccessful();
});

test('admin can view exams in table', function () {
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->create();

    Livewire::test(ListExams::class)
        ->assertCanSeeTableRecords([$exam]);
});

test('admin can create exam for formation', function () {
    $formation = Formation::factory()->create(['title' => 'Laravel Advanced']);

    Livewire::test(CreateExam::class)
        ->fillForm([
            'examable_type' => Formation::class,
            'formation_id' => $formation->id,
            'examable_id' => $formation->id,
            'title' => 'Examen Final - Laravel Advanced',
            'description' => 'Examen final de certification',
            'instructions' => 'Lisez attentivement chaque question',
            'duration_minutes' => 120,
            'passing_score' => 70,
            'max_attempts' => 2,
            'randomize_questions' => false,
            'show_results_immediately' => true,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('exams', [
        'examable_type' => Formation::class,
        'examable_id' => $formation->id,
        'title' => 'Examen Final - Laravel Advanced',
        'passing_score' => 70,
    ]);
});

test('admin can create exam for section', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create(['title' => 'Section 1 - Les Bases']);

    Livewire::test(CreateExam::class)
        ->fillForm([
            'examable_type' => Section::class,
            'formation_id' => $formation->id,
            'examable_id' => $section->id,
            'title' => 'Examen Section 1',
            'description' => 'Test de validation de la section',
            'duration_minutes' => 60,
            'passing_score' => 70,
            'max_attempts' => 3,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('exams', [
        'examable_type' => Section::class,
        'examable_id' => $section->id,
        'title' => 'Examen Section 1',
    ]);
});

test('admin can create exam for chapter', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create();
    $chapter = Chapter::factory()->for($section)->create(['title' => 'Chapitre 1 - Introduction']);

    Livewire::test(CreateExam::class)
        ->fillForm([
            'examable_type' => Chapter::class,
            'formation_id' => $formation->id,
            'section_id' => $section->id,
            'examable_id' => $chapter->id,
            'title' => 'Examen Chapitre 1',
            'description' => 'Test de validation du chapitre',
            'duration_minutes' => 30,
            'passing_score' => 70,
            'max_attempts' => 3,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('exams', [
        'examable_type' => Chapter::class,
        'examable_id' => $chapter->id,
        'title' => 'Examen Chapitre 1',
    ]);
});

test('admin can filter exams by type', function () {
    $formation = Formation::factory()->create();
    $formationExam = Exam::factory()->forFormation($formation)->create();

    $chapter = Chapter::factory()->for(
        Section::factory()->for($formation)
    )->create();
    $chapterExam = Exam::factory()->for($chapter, 'examable')->create();

    Livewire::test(ListExams::class)
        ->filterTable('examable_type', Formation::class)
        ->assertCanSeeTableRecords([$formationExam])
        ->assertCanNotSeeTableRecords([$chapterExam]);
});

test('exam can be activated and deactivated', function () {
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->create(['is_active' => false]);

    expect($exam->is_active)->toBeFalse();

    $exam->update(['is_active' => true]);
    $exam->refresh();

    expect($exam->is_active)->toBeTrue();
});
