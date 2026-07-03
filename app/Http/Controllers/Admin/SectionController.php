<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Http\Requests\Admin\UpdateSectionRequest;
use App\Jobs\ExtractChapterPdf;
use App\Models\Formation;
use App\Models\Section;
use App\Services\ChapterMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

final class SectionController extends Controller
{
    private const array SORTABLE = ['title', 'order_position', 'created_at'];

    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct(private readonly ChapterMediaService $mediaService) {}

    public function index(Request $request): Response
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 10;

        $sections = Section::query()
            ->with('formation:id,title')
            ->withCount('chapters')
            ->when($request->query('search'), fn (Builder $query, string $search): Builder => $query
                ->where('title', 'like', "%{$search}%"))
            ->when($request->filled('formation_id'), fn (Builder $query): Builder => $query
                ->where('formation_id', $request->integer('formation_id')))
            ->when($request->filled('is_active'), fn (Builder $query): Builder => $query
                ->where('is_active', $request->boolean('is_active')))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Sections/Index', [
            'sections' => $sections,
            'formations' => $this->formationOptions(),
            'filters' => $request->only('search', 'formation_id', 'is_active', 'sort', 'dir', 'per_page'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Sections/Form', [
            'section' => null,
            'formations' => $this->formationOptions(),
            'preselectedFormationId' => $request->integer('formation_id') ?: null,
        ]);
    }

    public function edit(Section $section): Response
    {
        $section->load(['chapters' => fn ($query) => $query->orderBy('order_position')]);

        return Inertia::render('Admin/Sections/Form', [
            'section' => [
                ...$section->only(['id', 'formation_id', 'title', 'description', 'duration', 'is_active']),
                'chapters' => $section->chapters->map(fn ($chapter): array => [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'content_type' => $chapter->content_type,
                    'content' => $chapter->content,
                    'video_url' => $chapter->video_url,
                    'media_url' => $chapter->media_url,
                    'processing_status' => $chapter->processing_status,
                    'processing_error' => $chapter->processing_error,
                    'processing_metadata' => $chapter->processing_metadata,
                    'processed_at' => $chapter->processed_at,
                    'duration_minutes' => $chapter->duration_minutes,
                    'is_free' => $chapter->is_free,
                    'is_active' => $chapter->is_active,
                ]),
            ],
            'formations' => $this->formationOptions(),
            'preselectedFormationId' => null,
        ]);
    }

    public function show(Section $section): Response
    {
        $section->load(['formation:id,title', 'chapters' => fn ($query) => $query->orderBy('order_position')]);

        return Inertia::render('Admin/Sections/Show', [
            'section' => [
                ...$section->only(['id', 'title', 'description', 'duration', 'order_position', 'is_active', 'created_at']),
                'formation' => [
                    'id' => $section->formation->id,
                    'title' => $section->formation->title,
                ],
                'has_exam' => $section->exam()->exists(),
                'chapters_count' => $section->chapters->count(),
                'chapters' => $section->chapters->map(fn ($chapter): array => [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'content_type' => $chapter->content_type,
                    'duration_minutes' => $chapter->duration_minutes,
                    'is_free' => $chapter->is_free,
                    'is_active' => $chapter->is_active,
                ]),
            ],
        ]);
    }

    public function store(StoreSectionRequest $request): RedirectResponse
    {
        $section = Section::query()->create($request->safe()->except('chapters'));
        $this->syncChapters($section, $request->validated('chapters') ?? []);

        return redirect()->route('admin.sections.index')->with('success', 'Section créée avec succès.');
    }

    public function update(UpdateSectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->safe()->except('chapters'));

        if ($request->has('chapters')) {
            $this->syncChapters($section, $request->validated('chapters') ?? []);
        }

        return redirect()->route('admin.sections.index')->with('success', 'Section mise à jour.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->chapters()->each(function ($chapter): void {
            $this->mediaService->deleteChapterFiles($chapter);
            $chapter->delete();
        });
        $section->delete();

        return back()->with('success', 'Section supprimée.');
    }

    public function toggleActive(Section $section): RedirectResponse
    {
        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('success', $section->is_active ? 'Section activée.' : 'Section désactivée.');
    }

    /**
     * Crée, met à jour et supprime les chapitres d'une section à partir du formulaire.
     *
     * @param  array<int, array{id?: int|null, title: string, content_type: string, content?: string|null, video?: UploadedFile|null, media?: UploadedFile|null, duration_minutes?: int|null, is_free?: bool, is_active?: bool}>  $chapters
     */
    private function syncChapters(Section $section, array $chapters): void
    {
        $keepIds = [];

        foreach (array_values($chapters) as $index => $chapter) {
            $existing = empty($chapter['id'])
                ? null
                : $section->chapters()->whereKey($chapter['id'])->first();

            $payload = $this->mediaService->prepare([
                'title' => $chapter['title'],
                'content_type' => $chapter['content_type'],
                'content' => $chapter['content'] ?? '',
                'video' => $chapter['video'] ?? null,
                'media' => $chapter['media'] ?? null,
                'duration_minutes' => isset($chapter['duration_minutes']) ? (int) $chapter['duration_minutes'] : null,
                'is_free' => $chapter['is_free'] ?? false,
                'is_active' => $chapter['is_active'] ?? true,
                'order_position' => $index + 1,
            ], $existing);

            if ($existing) {
                $existing->update($payload);
                $savedChapter = $existing->fresh();
            } else {
                $savedChapter = $section->chapters()->create($payload);
            }

            $keepIds[] = $savedChapter->id;
            if (($chapter['media'] ?? null) instanceof UploadedFile && $savedChapter->media_url) {
                ExtractChapterPdf::dispatch(
                    $savedChapter->id,
                    $savedChapter->media_url,
                    hash('sha256', (string) $savedChapter->content),
                )->afterCommit();
            }
        }

        $chaptersToDelete = $section->chapters();
        if ($keepIds !== []) {
            $chaptersToDelete->whereKeyNot($keepIds);
        }

        $chaptersToDelete->each(function ($chapter): void {
            $this->mediaService->deleteChapterFiles($chapter);
            $chapter->delete();
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{value: int, label: string}>
     */
    private function formationOptions(): \Illuminate\Support\Collection
    {
        return Formation::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Formation $formation): array => [
                'value' => $formation->id,
                'label' => $formation->title,
            ]);
    }
}
