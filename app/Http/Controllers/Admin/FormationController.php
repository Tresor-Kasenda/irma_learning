<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFormationRequest;
use App\Http\Requests\Admin\UpdateFormationRequest;
use App\Models\Formation;
use App\Services\FormationAssessmentReadinessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

final class FormationController extends Controller
{
    private const array SORTABLE = ['title', 'price', 'duration_hours', 'created_at'];

    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct(private readonly FormationAssessmentReadinessService $readiness) {}

    public function index(Request $request): Response
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 10;

        $formations = Formation::query()
            ->withCount(Formation::catalogCountRelations())
            ->when($request->query('search'), fn (Builder $query, string $search): Builder => $query
                ->where(fn (Builder $query): Builder => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")))
            ->when($request->filled('is_active'), fn (Builder $query): Builder => $query
                ->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('is_featured'), fn (Builder $query): Builder => $query
                ->where('is_featured', $request->boolean('is_featured')))
            ->when($request->query('difficulty_level'), fn (Builder $query, string $level): Builder => $query
                ->where('difficulty_level', $level))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Formations/Index', [
            'formations' => $formations,
            'filters' => $request->only('search', 'is_active', 'is_featured', 'difficulty_level', 'sort', 'dir', 'per_page'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Formations/Form', [
            'formation' => null,
        ]);
    }

    public function edit(Formation $formation): Response
    {
        $formation->load(['sections' => fn ($query) => $query->orderBy('order_position')->withCount('chapters')]);

        return Inertia::render('Admin/Formations/Form', [
            'formation' => [
                ...$formation->only([
                    'id', 'slug', 'title', 'short_description', 'description', 'image',
                    'difficulty_level', 'duration_hours', 'price', 'tags', 'is_active', 'is_featured', 'is_certifying',
                ]),
                'sections' => $formation->sections->map(fn ($section): array => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'duration' => $section->duration,
                    'is_active' => $section->is_active,
                    'chapters_count' => $section->chapters_count,
                ]),
            ],
        ]);
    }

    public function show(Formation $formation): Response
    {
        $formation->loadCount(['sections', 'chapters', 'enrollments']);
        $formation->load([
            'sections' => fn ($query) => $query
                ->orderBy('order_position')
                ->withCount('chapters')
                ->withExists('exam')
                ->with(['chapters' => fn ($query) => $query
                    ->orderBy('order_position')
                    ->select('id', 'section_id', 'title', 'content_type', 'duration_minutes', 'is_active')]),
        ]);

        return Inertia::render('Admin/Formations/Show', [
            'formation' => [
                ...$formation->only([
                    'id', 'slug', 'title', 'short_description', 'description', 'image',
                    'difficulty_level', 'duration_hours', 'price', 'tags', 'is_active', 'is_featured', 'is_certifying', 'created_at',
                ]),
                'sections_count' => $formation->sections_count,
                'chapters_count' => $formation->chapters_count,
                'enrollments_count' => $formation->enrollments_count,
                'sections' => $formation->sections->map(fn ($section): array => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'duration' => $section->duration,
                    'is_active' => $section->is_active,
                    'has_exam' => (bool) $section->exam_exists,
                    'chapters_count' => $section->chapters_count,
                    'chapters' => $section->chapters->map(fn ($chapter): array => [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'content_type' => $chapter->content_type,
                        'duration_minutes' => $chapter->duration_minutes,
                        'is_active' => $chapter->is_active,
                    ]),
                ]),
            ],
        ]);
    }

    public function store(StoreFormationRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image', 'sections');
        $data['image'] = $this->storeImage($request);

        $formation = Formation::query()->create($data);
        $this->syncSections($formation, $request->validated('sections') ?? []);

        $readinessIssues = $this->readiness->deactivateIfIncomplete($formation);

        return redirect()->route('admin.formations.index')->with(
            $readinessIssues === [] ? 'success' : 'info',
            $readinessIssues === []
                ? 'Formation créée avec succès.'
                : 'Formation enregistrée en brouillon. Ajoutez les évaluations obligatoires avant de l’activer.',
        );
    }

    public function destroy(Formation $formation): RedirectResponse
    {
        $this->deleteImage($formation->image);
        $formation->delete();

        return back()->with('success', 'Formation supprimée.');
    }

    public function toggleActive(Formation $formation): RedirectResponse
    {
        if (! $formation->is_active) {
            $readinessIssues = $this->readiness->issues($formation);

            if ($readinessIssues !== []) {
                return back()->with('error', implode(' ', $readinessIssues));
            }
        }

        $formation->update(['is_active' => ! $formation->is_active]);

        return back()->with('success', $formation->is_active ? 'Formation activée.' : 'Formation désactivée.');
    }

    public function update(UpdateFormationRequest $request, Formation $formation): RedirectResponse
    {
        $data = $request->safe()->except('image', 'sections');
        $previousImage = $formation->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $this->storeImage($request);
            $data['image'] = $newImage;
        }

        $formation->update($data);

        if ($newImage !== null && $newImage !== $previousImage) {
            $this->deleteImage($previousImage);
        }

        if ($request->has('sections')) {
            $this->syncSections($formation, $request->validated('sections') ?? []);
        }

        $readinessIssues = $this->readiness->deactivateIfIncomplete($formation);

        return redirect()->route('admin.formations.index')->with(
            $readinessIssues === [] ? 'success' : 'info',
            $readinessIssues === []
                ? 'Formation mise à jour.'
                : 'Formation mise à jour et replacée en brouillon : des évaluations obligatoires sont manquantes.',
        );
    }

    /**
     * Crée, met à jour et supprime les sections d'une formation à partir du formulaire.
     *
     * @param  array<int, array{id?: int|null, title: string, description?: string|null, duration?: int|null, is_active?: bool}>  $sections
     */
    private function syncSections(Formation $formation, array $sections): void
    {
        $keepIds = [];

        foreach (array_values($sections) as $index => $section) {
            $payload = [
                'title' => $section['title'],
                'description' => $section['description'] ?? null,
                'is_active' => $section['is_active'] ?? true,
                'order_position' => $index + 1,
            ];

            if (! empty($section['duration'])) {
                $payload['duration'] = (int) $section['duration'];
            }

            $existing = empty($section['id'])
                ? null
                : $formation->sections()->whereKey($section['id'])->first();

            if ($existing) {
                $existing->update($payload);
                $keepIds[] = $existing->id;
            } else {
                $keepIds[] = $formation->sections()->create($payload)->id;
            }
        }

        $formation->sections()->whereKeyNot($keepIds)->each(function ($section): void {
            $section->chapters()->delete();
            $section->delete();
        });
    }

    private function storeImage(Request $request): ?string
    {
        return $request->hasFile('image')
            ? $request->file('image')->store('formations', 'public')
            : null;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

}
