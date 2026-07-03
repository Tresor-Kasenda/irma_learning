<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChapterRequest;
use App\Http\Requests\Admin\UpdateChapterRequest;
use App\Jobs\ExtractChapterPdf;
use App\Models\Chapter;
use App\Models\Section;
use App\Services\ChapterMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

final class ChapterController extends Controller
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

        $chapters = Chapter::query()
            ->with('section:id,title,formation_id', 'section.formation:id,title')
            ->when($request->query('search'), fn (Builder $query, string $search): Builder => $query
                ->where('title', 'like', "%{$search}%"))
            ->when($request->filled('section_id'), fn (Builder $query): Builder => $query
                ->where('section_id', $request->integer('section_id')))
            ->when($request->query('content_type'), fn (Builder $query, string $type): Builder => $query
                ->where('content_type', $type))
            ->when($request->filled('is_active'), fn (Builder $query): Builder => $query
                ->where('is_active', $request->boolean('is_active')))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Chapters/Index', [
            'chapters' => $chapters,
            'sections' => $this->sectionOptions(),
            'filters' => $request->only('search', 'section_id', 'content_type', 'is_active', 'sort', 'dir', 'per_page'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Chapters/Form', [
            'chapter' => null,
            'sections' => $this->sectionOptions(),
            'preselectedSectionId' => $request->integer('section_id') ?: null,
        ]);
    }

    public function edit(Chapter $chapter): Response
    {
        return Inertia::render('Admin/Chapters/Form', [
            'chapter' => $chapter->only([
                'id', 'section_id', 'title', 'description', 'content', 'content_type',
                'video_url', 'media_url', 'duration_minutes', 'is_free', 'is_active',
                'processing_status', 'processing_error', 'processing_metadata', 'processed_at',
            ]),
            'sections' => $this->sectionOptions(),
            'preselectedSectionId' => null,
        ]);
    }

    public function show(Chapter $chapter): Response
    {
        $chapter->load('section:id,title,formation_id', 'section.formation:id,title');

        return Inertia::render('Admin/Chapters/Show', [
            'chapter' => [
                ...$chapter->only([
                    'id', 'title', 'description', 'content', 'content_type',
                    'video_url', 'media_url', 'cover_image', 'duration_minutes',
                    'is_free', 'is_active', 'order_position', 'created_at',
                ]),
                'content_html' => $chapter->getHtmlContentRaw(),
                'section' => [
                    'id' => $chapter->section->id,
                    'title' => $chapter->section->title,
                    'formation' => [
                        'id' => $chapter->section->formation->id,
                        'title' => $chapter->section->formation->title,
                    ],
                ],
            ],
        ]);
    }

    public function store(StoreChapterRequest $request): RedirectResponse
    {
        $data = $this->mediaService->prepare($request->validated());

        $chapter = Chapter::query()->create($data);
        if ($request->hasFile('media')) {
            $this->dispatchPdfExtraction($chapter);
        }

        return redirect()->route('admin.chapters.index')->with(
            'success',
            $request->hasFile('media')
                ? 'Chapitre créé. L’extraction Python du PDF a été ajoutée à la file.'
                : 'Chapitre créé avec succès.',
        );
    }

    public function update(UpdateChapterRequest $request, Chapter $chapter): RedirectResponse
    {
        $data = $this->mediaService->prepare($request->validated(), $chapter);

        $chapter->update($data);
        if ($request->hasFile('media')) {
            $this->dispatchPdfExtraction($chapter->fresh());
        }

        return redirect()->route('admin.chapters.index')->with('success', 'Chapitre mis à jour.');
    }

    public function destroy(Chapter $chapter): RedirectResponse
    {
        $this->mediaService->deleteChapterFiles($chapter);
        $chapter->delete();

        return back()->with('success', 'Chapitre supprimé.');
    }

    public function toggleActive(Chapter $chapter): RedirectResponse
    {
        $chapter->update(['is_active' => ! $chapter->is_active]);

        return back()->with('success', $chapter->is_active ? 'Chapitre activé.' : 'Chapitre désactivé.');
    }

    public function extractPdf(Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->content_type->value === 'pdf' && $chapter->media_url, 422);

        $chapter->update([
            'processing_status' => 'pending',
            'processing_error' => null,
            'processing_started_at' => null,
            'processed_at' => null,
        ]);
        $this->dispatchPdfExtraction($chapter);

        return back()->with('success', 'La nouvelle extraction Python a été ajoutée à la file.');
    }

    private function dispatchPdfExtraction(Chapter $chapter): void
    {
        if (! $chapter->media_url) {
            return;
        }

        ExtractChapterPdf::dispatch(
            $chapter->id,
            $chapter->media_url,
            hash('sha256', (string) $chapter->content),
        )->afterCommit();
    }

    /**
     * @return Collection<int, array{value: int, label: string}>
     */
    private function sectionOptions(): Collection
    {
        return Section::query()
            ->with('formation:id,title')
            ->orderBy('formation_id')
            ->orderBy('order_position')
            ->get(['id', 'title', 'formation_id'])
            ->map(fn (Section $section): array => [
                'value' => $section->id,
                'label' => ($section->formation?->title ? $section->formation->title.' — ' : '').$section->title,
            ]);
    }
}
