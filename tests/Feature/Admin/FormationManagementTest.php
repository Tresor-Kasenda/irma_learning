<?php

declare(strict_types=1);

use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('guests are redirected to login', function () {
    $this->get(route('admin.formations.index'))->assertRedirect(route('login'));
});

test('a student cannot access the formations admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.formations.index'))
        ->assertForbidden();
});

test('the index lists formations', function () {
    Formation::factory()->create(['title' => 'Laravel']);
    Formation::factory()->create(['title' => 'Vue']);

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Formations/Index')
            ->has('formations.data', 2)
            ->etc());
});

test('the index exposes the id required by formation actions', function () {
    $formation = Formation::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('formations.data.0.id', $formation->id)
            ->etc());
});

test('the index paginates ten formations by default', function () {
    Formation::factory()->count(12)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('formations.data', 10)
            ->where('formations.per_page', 10)
            ->where('formations.total', 12)
            ->etc());
});

test('the index accepts supported page sizes and rejects unsupported ones', function () {
    Formation::factory()->count(12)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index', ['per_page' => 25]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('formations.data', 12)
            ->where('formations.per_page', 25)
            ->where('filters.per_page', '25')
            ->etc());

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index', ['per_page' => 500]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('formations.data', 10)
            ->where('formations.per_page', 10)
            ->etc());
});

test('the index filters by status and search', function () {
    Formation::factory()->create(['title' => 'Laravel avancé', 'is_active' => true]);
    Formation::factory()->create(['title' => 'Vue basique', 'is_active' => false]);

    $this->actingAs($this->admin)
        ->get(route('admin.formations.index', ['is_active' => '1', 'search' => 'Laravel']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('formations.data', 1)
            ->where('formations.data.0.title', 'Laravel avancé')
            ->etc());
});

test('the create page renders a blank form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.formations.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Formations/Form')
            ->where('formation', null)
            ->etc());
});

test('the detail page renders the formation with its program', function () {
    $formation = Formation::factory()->create(['title' => 'À consulter']);
    $section = Section::factory()->for($formation)->create(['title' => 'Section 1', 'order_position' => 1]);

    $this->actingAs($this->admin)
        ->get(route('admin.formations.show', $formation->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Formations/Show')
            ->where('formation.title', 'À consulter')
            ->where('formation.sections_count', 1)
            ->where('formation.sections.0.id', $section->id)
            ->has('formation.enrollments_count')
            ->etc());
});

test('the edit page renders the formation', function () {
    $formation = Formation::factory()->create(['title' => 'À éditer']);

    $this->actingAs($this->admin)
        ->get(route('admin.formations.edit', $formation->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Formations/Form')
            ->where('formation.id', $formation->id)
            ->where('formation.title', 'À éditer')
            ->etc());
});

test('an admin can create a formation', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.formations.store'), [
            'title' => 'Nouvelle formation',
            'description' => 'Contenu détaillé.',
            'difficulty_level' => 'beginner',
            'duration_hours' => 10,
            'price' => 50,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertRedirect(route('admin.formations.index'))
        ->assertSessionHas('success', 'Formation créée avec succès.');

    expect(Formation::where('title', 'Nouvelle formation')->exists())->toBeTrue();
});

test('creating a formation validates required fields', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.formations.index'))
        ->post(route('admin.formations.store'), ['title' => ''])
        ->assertSessionHasErrors(['title', 'description', 'duration_hours', 'difficulty_level']);
});

test('an admin can store a formation image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.formations.store'), [
            'title' => 'Avec image',
            'description' => 'Contenu.',
            'difficulty_level' => 'beginner',
            'duration_hours' => 5,
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]);

    $formation = Formation::where('title', 'Avec image')->first();

    expect($formation->image)->not->toBeNull();
    Storage::disk('public')->assertExists($formation->image);
});

test('an admin can update a formation', function () {
    $formation = Formation::factory()->create(['title' => 'Ancien titre']);

    $this->actingAs($this->admin)
        ->post(route('admin.formations.update', $formation->id), [
            'title' => 'Nouveau titre',
            'description' => $formation->description,
            'difficulty_level' => $formation->difficulty_level->value,
            'duration_hours' => $formation->duration_hours,
            'is_active' => true,
            'is_featured' => false,
        ])
        ->assertSessionHas('success', 'Formation mise à jour.');

    expect($formation->fresh()->title)->toBe('Nouveau titre');
});

test('an admin can replace a formation image during update', function () {
    Storage::fake('public');
    Storage::disk('public')->put('formations/ancienne-couverture.jpg', 'ancienne image');

    $formation = Formation::factory()->create([
        'image' => 'formations/ancienne-couverture.jpg',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.formations.update', $formation->id), [
            'title' => $formation->title,
            'description' => $formation->description,
            'difficulty_level' => $formation->difficulty_level->value,
            'duration_hours' => $formation->duration_hours,
            'image' => UploadedFile::fake()->image('nouvelle-couverture-avec-un-nom-tres-long.jpg'),
        ])
        ->assertRedirect(route('admin.formations.index'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Formation mise à jour.');

    $newImage = $formation->fresh()->image;

    expect($newImage)->not->toBe('formations/ancienne-couverture.jpg');
    Storage::disk('public')->assertMissing('formations/ancienne-couverture.jpg');
    Storage::disk('public')->assertExists($newImage);
});

test('an admin can toggle a formation active state', function () {
    $formation = Formation::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.formations.toggle-active', $formation->id));

    expect($formation->fresh()->is_active)->toBeFalse();
});

test('an admin can delete a formation', function () {
    $formation = Formation::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.formations.destroy', $formation->id))
        ->assertSessionHas('success');

    expect(Formation::find($formation->id))->toBeNull();
});

test('an admin can create a formation with several sections at once', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.formations.store'), [
            'title' => 'Parcours complet',
            'description' => 'Contenu.',
            'difficulty_level' => 'beginner',
            'duration_hours' => 12,
            'sections' => [
                ['title' => 'Introduction'],
                ['title' => 'Approfondissement'],
            ],
        ])
        ->assertRedirect(route('admin.formations.index'));

    $formation = Formation::where('title', 'Parcours complet')->first();

    expect($formation->sections()->count())->toBe(2)
        ->and($formation->sections()->orderBy('order_position')->pluck('title')->all())
        ->toBe(['Introduction', 'Approfondissement']);
});

test('creating a formation persists tags and full section fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.formations.store'), [
            'title' => 'Formation complète',
            'description' => 'Contenu.',
            'difficulty_level' => 'intermediate',
            'duration_hours' => 8,
            'tags' => ['php', 'laravel'],
            'sections' => [
                ['title' => 'Intro', 'description' => 'Description intro', 'duration' => 30, 'is_active' => false],
            ],
        ])
        ->assertRedirect(route('admin.formations.index'));

    $formation = Formation::where('title', 'Formation complète')->first();
    expect($formation->tags)->toBe(['php', 'laravel']);

    $section = $formation->sections()->first();
    expect($section->description)->toBe('Description intro')
        ->and($section->duration)->toBe(30)
        ->and($section->is_active)->toBeFalse();
});

test('updating a formation syncs its sections (add, rename, remove)', function () {
    $formation = Formation::factory()->create();
    $kept = Section::factory()->for($formation)->create(['title' => 'Garde', 'order_position' => 1]);
    $removed = Section::factory()->for($formation)->create(['title' => 'À retirer', 'order_position' => 2]);

    $this->actingAs($this->admin)
        ->post(route('admin.formations.update', $formation->id), [
            'title' => $formation->title,
            'description' => $formation->description,
            'difficulty_level' => $formation->difficulty_level->value,
            'duration_hours' => $formation->duration_hours,
            'sections' => [
                ['id' => $kept->id, 'title' => 'Garde renommée'],
                ['title' => 'Nouvelle section'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($formation->sections()->count())->toBe(2)
        ->and($kept->fresh()->title)->toBe('Garde renommée')
        ->and(Section::find($removed->id))->toBeNull()
        ->and($formation->sections()->where('title', 'Nouvelle section')->exists())->toBeTrue();
});

test('sections can share a title across different formations', function () {
    $other = Formation::factory()->create();
    Section::factory()->for($other)->create(['title' => 'Introduction']);

    $formation = Formation::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.formations.update', $formation->id), [
            'title' => $formation->title,
            'description' => $formation->description,
            'difficulty_level' => $formation->difficulty_level->value,
            'duration_hours' => $formation->duration_hours,
            'sections' => [['title' => 'Introduction']],
        ])
        ->assertSessionHasNoErrors();

    expect($formation->sections()->where('title', 'Introduction')->exists())->toBeTrue();
});

test('duplicate section titles in the same submission are rejected', function () {
    $formation = Formation::factory()->create();

    $this->actingAs($this->admin)
        ->from(route('admin.formations.edit', $formation->id))
        ->post(route('admin.formations.update', $formation->id), [
            'title' => $formation->title,
            'description' => $formation->description,
            'difficulty_level' => $formation->difficulty_level->value,
            'duration_hours' => $formation->duration_hours,
            'sections' => [['title' => 'Doublon'], ['title' => 'Doublon']],
        ])
        ->assertSessionHasErrors('sections.1.title');
});
