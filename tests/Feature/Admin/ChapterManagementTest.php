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
    $this->section = Section::factory()->for($this->formation)->create();
});

test('guests are redirected to login', function () {
    $this->get(route('admin.chapters.index'))->assertRedirect(route('login'));
});

test('a student cannot access the chapters admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.chapters.index'))
        ->assertForbidden();
});

test('the index lists chapters with their section', function () {
    Chapter::factory()->for($this->section)->create(['title' => 'Intro']);

    $this->actingAs($this->admin)
        ->get(route('admin.chapters.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Chapters/Index')
            ->has('chapters.data', 1)
            ->where('chapters.data.0.title', 'Intro')
            ->has('sections')
            ->etc());
});

test('the index filters by section and content type', function () {
    $other = Section::factory()->for($this->formation)->create();
    Chapter::factory()->for($this->section)->create(['title' => 'Gardé', 'content_type' => 'text']);
    Chapter::factory()->for($other)->create(['title' => 'Autre']);

    $this->actingAs($this->admin)
        ->get(route('admin.chapters.index', ['section_id' => $this->section->id, 'content_type' => 'text']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('chapters.data', 1)
            ->where('chapters.data.0.title', 'Gardé')
            ->etc());
});

test('the create page provides sections and can preselect one', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.chapters.create', ['section_id' => $this->section->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Chapters/Form')
            ->where('chapter', null)
            ->where('preselectedSectionId', $this->section->id)
            ->has('sections')
            ->etc());
});

test('an admin can create a text chapter', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.chapters.store'), [
            'section_id' => $this->section->id,
            'title' => 'Chapitre texte',
            'content_type' => 'text',
            'content' => 'Le contenu du chapitre.',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.chapters.index'))
        ->assertSessionHas('success');

    $chapter = Chapter::where('title', 'Chapitre texte')->first();

    expect($chapter->section_id)->toBe($this->section->id)
        ->and($chapter->content)->toBe('Le contenu du chapitre.')
        ->and($chapter->content_type->value)->toBe('text');
});

test('a video chapter created without a file keeps an empty content', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.chapters.store'), [
            'section_id' => $this->section->id,
            'title' => 'Chapitre vidéo',
            'content_type' => 'video',
            'content' => 'ignoré pour la vidéo',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.chapters.index'));

    $chapter = Chapter::where('title', 'Chapitre vidéo')->first();

    expect($chapter->content_type->value)->toBe('video')
        ->and($chapter->content)->toBe('');
});

test('an admin can upload a video for a chapter', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.store'), [
            'section_id' => $this->section->id,
            'title' => 'Chapitre vidéo uploadé',
            'content_type' => 'video',
            'video' => UploadedFile::fake()->create('cours.mp4', 512, 'video/mp4'),
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.chapters.index'))
        ->assertSessionHasNoErrors();

    $chapter = Chapter::where('title', 'Chapitre vidéo uploadé')->firstOrFail();

    expect($chapter->video_url)->not->toBeNull();
    Storage::disk('public')->assertExists($chapter->video_url);
});

test('uploading a pdf queues its Python extraction', function () {
    Queue::fake();
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.store'), [
            'section_id' => $this->section->id,
            'title' => 'Support Python',
            'content_type' => 'pdf',
            'media' => UploadedFile::fake()->create('support.pdf', 256, 'application/pdf'),
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.chapters.index'))
        ->assertSessionHasNoErrors();

    $chapter = Chapter::where('title', 'Support Python')->firstOrFail();

    expect($chapter->processing_status)->toBe('pending');
    Queue::assertPushed(ExtractChapterPdf::class, fn (ExtractChapterPdf $job): bool => $job->chapterId === $chapter->id);
});

test('the queued Python extraction stores markdown metadata and duration', function () {
    Storage::fake('public');
    Storage::disk('public')->put('chapters/support.pdf', 'fake-pdf');
    Storage::disk('public')->put('chapters/extracted/old/page.png', 'old-page');
    config()->set('learning.pdf_extraction.python_binary', 'python3');
    config()->set('learning.pdf_extraction.script_path', base_path('tests/Fixtures/fake_pdf_extractor.py'));

    $chapter = Chapter::factory()->for($this->section)->create([
        'content_type' => 'pdf',
        'content' => '',
        'media_url' => 'chapters/support.pdf',
        'processing_status' => 'pending',
        'processing_metadata' => ['asset_directory' => 'chapters/extracted/old'],
    ]);
    $job = new ExtractChapterPdf($chapter->id, $chapter->media_url, hash('sha256', ''));

    $job->handle(
        app(App\Services\PythonPdfExtractionService::class),
        app(App\Services\ReadingDurationCalculatorService::class),
    );

    $chapter->refresh();

    expect($chapter->processing_status)->toBe('completed')
        ->and($chapter->content)->toContain('# Contenu Python')
        ->and($chapter->content)->toContain('](/storage/chapters/extracted/')
        ->and($chapter->content)->not->toContain('http://localhost')
        ->and($chapter->duration_minutes)->toBeGreaterThan(0)
        ->and($chapter->processing_metadata['page_count'])->toBe(1)
        ->and($chapter->cover_image)->not->toBeNull()
        ->and($chapter->markdown_file)->not->toBeNull();
    Storage::disk('public')->assertExists($chapter->cover_image);
    Storage::disk('public')->assertExists($chapter->markdown_file);
    Storage::disk('public')->assertMissing('chapters/extracted/old/page.png');
});

test('replacing a pdf keeps the previous extracted assets until the new job succeeds', function () {
    Queue::fake();
    Storage::fake('public');
    Storage::disk('public')->put('chapters/old.pdf', 'old-pdf');
    Storage::disk('public')->put('chapters/extracted/old/page.png', 'old-page');
    $chapter = Chapter::factory()->for($this->section)->create([
        'content_type' => 'pdf',
        'media_url' => 'chapters/old.pdf',
        'cover_image' => 'chapters/extracted/old/page.png',
        'processing_status' => 'completed',
        'processing_metadata' => ['asset_directory' => 'chapters/extracted/old'],
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.update', $chapter), [
            'section_id' => $this->section->id,
            'title' => $chapter->title,
            'content_type' => 'pdf',
            'content' => $chapter->content,
            'media' => UploadedFile::fake()->create('replacement.pdf', 256, 'application/pdf'),
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($chapter->fresh()->processing_status)->toBe('pending')
        ->and($chapter->fresh()->processing_metadata['asset_directory'])->toBe('chapters/extracted/old');
    Storage::disk('public')->assertExists('chapters/extracted/old/page.png');
    Queue::assertPushed(ExtractChapterPdf::class);
});

test('an admin can retry a failed pdf extraction', function () {
    Queue::fake();
    $chapter = Chapter::factory()->for($this->section)->create([
        'content_type' => 'pdf',
        'media_url' => 'chapters/support.pdf',
        'processing_status' => 'failed',
        'processing_error' => 'Erreur précédente',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.extract-pdf', $chapter))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($chapter->fresh()->processing_status)->toBe('pending')
        ->and($chapter->fresh()->processing_error)->toBeNull();
    Queue::assertPushed(ExtractChapterPdf::class);
});

test('an admin can edit markdown attached to a pdf without replacing the file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('chapters/support.pdf', 'pdf');
    $chapter = Chapter::factory()->for($this->section)->create([
        'content_type' => 'pdf',
        'content' => 'Contenu extrait',
        'media_url' => 'chapters/support.pdf',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.update', $chapter), [
            'section_id' => $this->section->id,
            'title' => $chapter->title,
            'content_type' => 'pdf',
            'content' => '# Contenu PDF corrigé',
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($chapter->fresh()->content)->toBe('# Contenu PDF corrigé')
        ->and($chapter->fresh()->media_url)->toBe('chapters/support.pdf');
    Storage::disk('public')->assertExists('chapters/support.pdf');
});

test('creating a chapter validates required fields', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.chapters.create'))
        ->post(route('admin.chapters.store'), ['title' => ''])
        ->assertSessionHasErrors(['section_id', 'title', 'content_type']);
});

test('the detail page renders the chapter with its section and formation', function () {
    $chapter = Chapter::factory()->for($this->section)->create([
        'title' => 'À consulter',
        'content' => "# Introduction\n\nContenu **important**.",
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.chapters.show', $chapter))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Chapters/Show')
            ->where('chapter.title', 'À consulter')
            ->where('chapter.content_html', fn (string $html): bool => str_contains($html, '<h1>Introduction</h1>')
                && str_contains($html, '<strong>important</strong>'))
            ->where('chapter.section.id', $this->section->id)
            ->where('chapter.section.formation.id', $this->formation->id)
            ->etc());
});

test('an admin can update a chapter', function () {
    $chapter = Chapter::factory()->for($this->section)->create(['content_type' => 'text']);

    $this->actingAs($this->admin)
        ->post(route('admin.chapters.update', $chapter), [
            'section_id' => $this->section->id,
            'title' => 'Titre mis à jour',
            'content_type' => 'text',
            'content' => 'Nouveau contenu',
            'is_active' => true,
        ])
        ->assertSessionHas('success');

    expect($chapter->fresh()->title)->toBe('Titre mis à jour')
        ->and($chapter->fresh()->content)->toBe('Nouveau contenu');
});

test('an admin can toggle a chapter active state', function () {
    $chapter = Chapter::factory()->for($this->section)->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.chapters.toggle-active', $chapter));

    expect($chapter->fresh()->is_active)->toBeFalse();
});

test('an admin can delete a chapter', function () {
    $chapter = Chapter::factory()->for($this->section)->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.chapters.destroy', $chapter))
        ->assertSessionHas('success');

    expect(Chapter::find($chapter->id))->toBeNull();
});
