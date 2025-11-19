<?php

declare(strict_types=1);

use App\Filament\Resources\ChapterResource\Pages\CreateChapter;
use App\Filament\Resources\ChapterResource\Pages\ListChapters;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('can render the chapter list page', function () {
    Livewire::test(ListChapters::class)
        ->assertSuccessful();
});

it('can list chapters', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);
    $chapters = Chapter::factory()->count(3)->create([
        'section_id' => $section->id,
    ]);

    Livewire::test(ListChapters::class)
        ->assertCanSeeTableRecords($chapters);
});

it('can create a text chapter', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);

    Livewire::test(CreateChapter::class)
        ->fillForm([
            'section_id' => $section->id,
            'title' => 'Test Chapter',
            'content_type' => 'text',
            'content' => 'This is test content',
            'duration_minutes' => 15,
            'is_active' => true,
            'is_free' => false,
        ])
        ->call('create')
        ->assertHasNoErrors();

    expect(Chapter::where('title', 'Test Chapter')->exists())->toBeTrue();
});

it('can delete a chapter', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);
    $chapter = Chapter::factory()->create([
        'section_id' => $section->id,
    ]);

    Livewire::test(ListChapters::class)
        ->callTableAction('delete', $chapter);

    expect(Chapter::find($chapter->id))->toBeNull();
});

it('can filter chapters by section', function () {
    $formation = Formation::factory()->create();
    $section1 = Section::factory()->create(['formation_id' => $formation->id]);
    $section2 = Section::factory()->create(['formation_id' => $formation->id]);

    $chapter1 = Chapter::factory()->create(['section_id' => $section1->id]);
    $chapter2 = Chapter::factory()->create(['section_id' => $section2->id]);

    Livewire::test(ListChapters::class)
        ->filterTable('section_id', $section1->id)
        ->assertCanSeeTableRecords([$chapter1])
        ->assertCanNotSeeTableRecords([$chapter2]);
});

it('can filter chapters by content type', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);

    $textChapter = Chapter::factory()->create([
        'section_id' => $section->id,
        'content_type' => 'text',
    ]);

    $pdfChapter = Chapter::factory()->create([
        'section_id' => $section->id,
        'content_type' => 'pdf',
    ]);

    Livewire::test(ListChapters::class)
        ->filterTable('content_type', 'text')
        ->assertCanSeeTableRecords([$textChapter])
        ->assertCanNotSeeTableRecords([$pdfChapter]);
});

it('can toggle chapter active status', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);
    $chapter = Chapter::factory()->create([
        'section_id' => $section->id,
        'is_active' => true,
    ]);

    Livewire::test(ListChapters::class)
        ->callTableBulkAction('toggle_active', [$chapter]);

    expect($chapter->fresh()->is_active)->toBeFalse();
});

it('can toggle chapter free status', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);
    $chapter = Chapter::factory()->create([
        'section_id' => $section->id,
        'is_free' => false,
    ]);

    Livewire::test(ListChapters::class)
        ->callTableBulkAction('toggle_free', [$chapter]);

    expect($chapter->fresh()->is_free)->toBeTrue();
});

it('automatically sets order position when creating chapter', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->create(['formation_id' => $formation->id]);

    Chapter::factory()->create([
        'section_id' => $section->id,
        'order_position' => 1,
    ]);

    Chapter::factory()->create([
        'section_id' => $section->id,
        'order_position' => 2,
    ]);

    $newChapter = Chapter::create([
        'section_id' => $section->id,
        'title' => 'New Chapter',
        'content_type' => 'text',
        'content' => 'Test content',
        'duration_minutes' => 10,
    ]);

    expect($newChapter->order_position)->toBe(3);
});
