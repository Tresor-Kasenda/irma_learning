<?php

declare(strict_types=1);

use App\Jobs\ExtractChapterPdf;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->formation = Formation::factory()->create();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.sections.index'))->assertRedirect(route('login'));
});

test('a student cannot access the sections admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.sections.index'))
        ->assertForbidden();
});

test('the index lists sections with their formation', function () {
    Section::factory()->for($this->formation)->create(['title' => 'Intro']);

    $this->actingAs($this->admin)
        ->get(route('admin.sections.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Sections/Index')
            ->has('sections.data', 1)
            ->where('sections.data.0.title', 'Intro')
            ->has('formations')
            ->etc());
});

test('the index filters by formation and status', function () {
    $other = Formation::factory()->create();
    Section::factory()->for($this->formation)->create(['title' => 'Gardée', 'is_active' => true]);
    Section::factory()->for($other)->create(['title' => 'Autre']);

    $this->actingAs($this->admin)
        ->get(route('admin.sections.index', ['formation_id' => $this->formation->id, 'is_active' => '1']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('sections.data', 1)
            ->where('sections.data.0.title', 'Gardée')
            ->etc());
});

test('the create page provides the formations list', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.sections.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Sections/Form')
            ->where('section', null)
            ->has('formations')
            ->etc());
});

test('the create page can preselect a formation', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.sections.create', ['formation_id' => $this->formation->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('preselectedFormationId', $this->formation->id)
            ->etc());
});

test('an admin can create a section with chapters', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.sections.store'), [
            'formation_id' => $this->formation->id,
            'title' => 'Nouvelle section',
            'is_active' => true,
            'chapters' => [
                [
                    'title' => 'Chapitre 1',
                    'content_type' => 'text',
                    'content' => "# Introduction\n\nContenu **Markdown**.",
                    'duration_minutes' => 1,
                    'is_active' => true,
                    'is_free' => true,
                ],
                ['title' => 'Chapitre 2', 'content_type' => 'video', 'duration_minutes' => 12],
            ],
        ])
        ->assertRedirect(route('admin.sections.index'))
        ->assertSessionHas('success');

    $section = Section::where('title', 'Nouvelle section')->first();

    expect($section->formation_id)->toBe($this->formation->id)
        ->and($section->chapters()->count())->toBe(2)
        ->and($section->chapters()->orderBy('order_position')->pluck('title')->all())
        ->toBe(['Chapitre 1', 'Chapitre 2'])
        ->and($section->chapters()->orderBy('order_position')->first()->content)
        ->toBe("# Introduction\n\nContenu **Markdown**.");
});

test('an admin can upload chapter media from the section form', function () {
    Queue::fake();
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.sections.store'), [
            'formation_id' => $this->formation->id,
            'title' => 'Section avec médias',
            'chapters' => [
                [
                    'title' => 'Vidéo',
                    'content_type' => 'video',
                    'video' => UploadedFile::fake()->create('cours.mp4', 512, 'video/mp4'),
                ],
                [
                    'title' => 'Support PDF',
                    'content_type' => 'pdf',
                    'content' => '# Contenu PDF révisable',
                    'media' => UploadedFile::fake()->create('support.pdf', 256, 'application/pdf'),
                ],
            ],
        ])
        ->assertRedirect(route('admin.sections.index'))
        ->assertSessionHasNoErrors();

    $section = Section::where('title', 'Section avec médias')->firstOrFail();
    $videoChapter = $section->chapters()->where('content_type', 'video')->firstOrFail();
    $pdfChapter = $section->chapters()->where('content_type', 'pdf')->firstOrFail();

    expect($videoChapter->video_url)->not->toBeNull()
        ->and($pdfChapter->media_url)->not->toBeNull()
        ->and($pdfChapter->content)->toBe('# Contenu PDF révisable');
    Storage::disk('public')->assertExists($videoChapter->video_url);
    Storage::disk('public')->assertExists($pdfChapter->media_url);
    Queue::assertPushed(ExtractChapterPdf::class, fn (ExtractChapterPdf $job): bool => $job->chapterId === $pdfChapter->id);
});

test('the edit page provides chapter markdown content', function () {
    $section = Section::factory()->for($this->formation)->create();
    Chapter::factory()->for($section)->create([
        'content_type' => 'text',
        'content' => "```mermaid\nflowchart LR\nA --> B\n```",
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.sections.edit', $section))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Sections/Form')
            ->where('section.chapters.0.content', "```mermaid\nflowchart LR\nA --> B\n```")
            ->etc());
});

test('creating a section validates the formation and title', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.sections.create'))
        ->post(route('admin.sections.store'), ['title' => ''])
        ->assertSessionHasErrors(['formation_id', 'title']);
});

test('the detail page renders the section with its chapters', function () {
    $section = Section::factory()->for($this->formation)->create(['title' => 'À consulter']);
    $chapter = Chapter::factory()->for($section)->create(['title' => 'Chapitre A']);

    $this->actingAs($this->admin)
        ->get(route('admin.sections.show', $section))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Sections/Show')
            ->where('section.title', 'À consulter')
            ->where('section.chapters_count', 1)
            ->where('section.chapters.0.id', $chapter->id)
            ->where('section.formation.id', $this->formation->id)
            ->etc());
});

test('an admin can update a section and sync its chapters', function () {
    $section = Section::factory()->for($this->formation)->create();
    $kept = Chapter::factory()->for($section)->create(['title' => 'Gardé', 'duration_minutes' => 45, 'order_position' => 1]);
    $removed = Chapter::factory()->for($section)->create(['title' => 'À retirer', 'order_position' => 2]);

    $this->actingAs($this->admin)
        ->post(route('admin.sections.update', $section), [
            'formation_id' => $this->formation->id,
            'title' => 'Section mise à jour',
            'chapters' => [
                [
                    'id' => $kept->id,
                    'title' => 'Gardé renommé',
                    'content_type' => 'text',
                    'content' => 'Nouveau contenu Markdown',
                ],
                ['title' => 'Nouveau chapitre', 'content_type' => 'pdf'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($section->fresh()->title)->toBe('Section mise à jour')
        ->and($section->chapters()->count())->toBe(2)
        ->and($kept->fresh()->title)->toBe('Gardé renommé')
        ->and($kept->fresh()->content)->toBe('Nouveau contenu Markdown')
        ->and($kept->fresh()->duration_minutes)->toBeNull()
        ->and(Chapter::find($removed->id))->toBeNull();
});

test('an admin can toggle a section active state', function () {
    $section = Section::factory()->for($this->formation)->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.sections.toggle-active', $section));

    expect($section->fresh()->is_active)->toBeFalse();
});

test('an admin can delete a section and its chapters', function () {
    $section = Section::factory()->for($this->formation)->create();
    $chapter = Chapter::factory()->for($section)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.sections.destroy', $section))
        ->assertSessionHas('success');

    expect(Section::find($section->id))->toBeNull()
        ->and(Chapter::find($chapter->id))->toBeNull();
});
