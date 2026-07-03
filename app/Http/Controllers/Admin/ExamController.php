<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ExamController extends Controller
{
    private const array SORTABLE = ['title', 'duration_minutes', 'passing_score', 'created_at'];

    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request): Response
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 10;

        $exams = Exam::query()
            ->with('examable')
            ->withCount('questions')
            ->when($request->query('search'), fn (Builder $query, string $search): Builder => $query
                ->where('title', 'like', "%{$search}%"))
            ->when($request->filled('examable_type'), fn (Builder $query): Builder => $query
                ->where('examable_type', 'App\Models\\'.$request->query('examable_type')))
            ->when($request->filled('is_active'), fn (Builder $query): Builder => $query
                ->where('is_active', $request->boolean('is_active')))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Exams/Index', [
            'exams' => $exams,
            'filters' => $request->only('search', 'examable_type', 'is_active', 'sort', 'dir', 'per_page'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Exams/Form', [
            'exam' => null,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function edit(Exam $exam): Response
    {
        $exam->load(['examable', 'questions' => fn ($q) => $q->with('options')->orderBy('order_position')]);

        return Inertia::render('Admin/Exams/Form', [
            'exam' => [
                ...$exam->only([
                    'id', 'title', 'description', 'instructions', 'duration_minutes',
                    'passing_score', 'max_attempts', 'randomize_questions',
                    'show_results_immediately', 'is_active', 'available_from', 'available_until',
                    'examable_type', 'examable_id',
                ]),
                'examable_label' => $this->examableLabel($exam),
                'questions' => $exam->questions->map(fn ($q): array => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type->value,
                    'points' => $q->points,
                    'is_required' => $q->is_required,
                    'explanation' => $q->explanation,
                    'options' => $q->options->map(fn ($opt): array => [
                        'id' => $opt->id,
                        'option_text' => $opt->option_text,
                        'is_correct' => $opt->is_correct,
                        'order_position' => $opt->order_position,
                    ]),
                ]),
            ],
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function show(Exam $exam): Response
    {
        $exam->loadCount('questions');
        $exam->load([
            'examable',
            'questions' => fn ($q) => $q->withCount('options')->orderBy('order_position'),
            'attempts' => fn ($q) => $q->with('user:id,name,email')->latest(),
        ]);

        return Inertia::render('Admin/Exams/Show', [
            'exam' => [
                ...$exam->only([
                    'id', 'title', 'description', 'instructions', 'duration_minutes',
                    'passing_score', 'max_attempts', 'randomize_questions',
                    'show_results_immediately', 'is_active', 'available_from', 'available_until', 'created_at',
                ]),
                'questions_count' => $exam->questions_count,
                'questions' => $exam->questions->map(fn ($q): array => [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_type' => $q->question_type->value,
                    'points' => $q->points,
                    'is_required' => $q->is_required,
                    'order_position' => $q->order_position,
                    'options_count' => $q->options_count,
                    'explanation' => $q->explanation,
                ]),
                'attempts' => $exam->attempts->map(fn ($a): array => [
                    'id' => $a->id,
                    'user' => $a->user ? ['id' => $a->user->id, 'name' => $a->user->name, 'email' => $a->user->email] : null,
                    'attempt_number' => $a->attempt_number,
                    'status' => $a->status->value,
                    'score' => $a->score,
                    'max_score' => $a->max_score,
                    'percentage' => $a->percentage,
                    'time_taken' => $a->time_taken,
                    'started_at' => $a->started_at?->toISOString(),
                    'completed_at' => $a->completed_at?->toISOString(),
                ]),
                'examable_label' => $this->examableLabel($exam),
                'total_points' => $exam->getTotalPoints(),
            ],
        ]);
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $exam = Exam::query()->create($request->safe()->except('questions'));
        $this->syncQuestions($exam, $request->validated('questions') ?? []);

        return redirect()->route('admin.exams.show', $exam->id)->with('success', 'Examen créé avec succès.');
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->update($request->safe()->except('questions'));
        $this->syncQuestions($exam, $request->validated('questions') ?? []);

        return redirect()->route('admin.exams.show', $exam->id)->with('success', 'Examen mis à jour.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->questions()->each(fn ($q) => $q->options()->delete());
        $exam->questions()->delete();
        $exam->attempts()->delete();
        $exam->delete();

        return back()->with('success', 'Examen supprimé.');
    }

    public function toggleActive(Exam $exam): RedirectResponse
    {
        $exam->update(['is_active' => ! $exam->is_active]);

        return back()->with('success', $exam->is_active ? 'Examen activé.' : 'Examen désactivé.');
    }

    public function duplicate(Exam $exam): RedirectResponse
    {
        $exam->load('questions.options');

        $duplicate = $exam->replicate(['created_at', 'updated_at']);
        $duplicate->title = $exam->title.' (copie)';
        $duplicate->is_active = false;
        $duplicate->save();

        foreach ($exam->questions as $question) {
            $newQuestion = $question->replicate(['exam_id', 'created_at', 'updated_at']);
            $newQuestion->exam_id = $duplicate->id;
            $newQuestion->save();

            foreach ($question->options as $option) {
                $newOption = $option->replicate(['question_id', 'created_at', 'updated_at']);
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }
        }

        return redirect()->route('admin.exams.show', $duplicate->id)
            ->with('success', 'Examen dupliqué avec succès.');
    }

    public function bulkActivate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        Exam::query()->whereKey($ids)->update(['is_active' => true]);

        return back()->with('success', 'Examens activés avec succès.');
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        Exam::query()->whereKey($ids)->update(['is_active' => false]);

        return back()->with('success', 'Examens désactivés avec succès.');
    }

    public function bulkDuplicate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        Exam::query()->whereKey($ids)->each(function (Exam $exam): void {
            $exam->load('questions.options');
            $duplicate = $exam->replicate(['created_at', 'updated_at']);
            $duplicate->title = $exam->title.' (copie)';
            $duplicate->is_active = false;
            $duplicate->save();

            foreach ($exam->questions as $question) {
                $newQuestion = $question->replicate(['exam_id', 'created_at', 'updated_at']);
                $newQuestion->exam_id = $duplicate->id;
                $newQuestion->save();

                foreach ($question->options as $option) {
                    $newOption = $option->replicate(['question_id', 'created_at', 'updated_at']);
                    $newOption->question_id = $newQuestion->id;
                    $newOption->save();
                }
            }
        });

        return back()->with('success', 'Examens dupliqués avec succès.');
    }

    /**
     * @param  array<int, array{id?: int|null, question_text: string, question_type: string, points: int, is_required?: bool, explanation?: string|null, options: array<int, array{id?: int|null, option_text: string, is_correct: bool}>}>  $questionsData
     */
    private function syncQuestions(Exam $exam, array $questionsData): void
    {
        $keepQuestionIds = [];

        foreach (array_values($questionsData) as $qIndex => $qData) {
            if (empty($qData['question_text'])) {
                continue;
            }

            $questionPayload = [
                'question_text' => $qData['question_text'],
                'question_type' => $qData['question_type'] ?? 'single_choice',
                'points' => $qData['points'] ?? 1,
                'is_required' => $qData['is_required'] ?? true,
                'explanation' => $qData['explanation'] ?? null,
                'order_position' => $qIndex + 1,
            ];

            $existingQuestion = empty($qData['id'])
                ? null
                : $exam->questions()->whereKey($qData['id'])->first();

            if ($existingQuestion) {
                $existingQuestion->update($questionPayload);
                $savedQuestion = $existingQuestion;
            } else {
                $savedQuestion = $exam->questions()->create($questionPayload);
            }

            $keepQuestionIds[] = $savedQuestion->id;

            $optionsData = $qData['options'] ?? [];
            $keepOptionIds = [];

            foreach (array_values($optionsData) as $oIndex => $oData) {
                if (empty($oData['option_text'])) {
                    continue;
                }

                $optionPayload = [
                    'option_text' => $oData['option_text'],
                    'is_correct' => $oData['is_correct'] ?? false,
                    'order_position' => $oIndex + 1,
                ];

                $existingOption = empty($oData['id'])
                    ? null
                    : $savedQuestion->options()->whereKey($oData['id'])->first();

                if ($existingOption) {
                    $existingOption->update($optionPayload);
                    $keepOptionIds[] = $existingOption->id;
                } else {
                    $keepOptionIds[] = $savedQuestion->options()->create($optionPayload)->id;
                }
            }

            $savedQuestion->options()->whereKeyNot($keepOptionIds)->delete();
        }

        $exam->questions()->whereKeyNot($keepQuestionIds)->each(function ($q): void {
            $q->options()->delete();
            $q->delete();
        });
    }

    private function examableLabel(Exam $exam): string
    {
        if (! $exam->examable) {
            return '—';
        }

        if ($exam->examable instanceof Section) {
            return 'Section : '.$exam->examable->title;
        }

        if ($exam->examable instanceof Formation) {
            return 'Formation : '.$exam->examable->title;
        }

        return '—';
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{value: string, label: string, group: string}>
     */
    private function parentOptions(): \Illuminate\Support\Collection
    {
        $sections = Section::query()
            ->with('formation:id,title')
            ->orderBy('title')
            ->get()
            ->map(fn (Section $section): array => [
                'value' => 'App\Models\Section:'.$section->id,
                'label' => $section->title,
                'group' => 'Section — '.($section->formation?->title ?? 'Sans formation'),
            ]);

        $formations = Formation::query()
            ->orderBy('title')
            ->get()
            ->map(fn (Formation $formation): array => [
                'value' => 'App\Models\Formation:'.$formation->id,
                'label' => $formation->title,
                'group' => 'Formation',
            ]);

        return $sections->concat($formations);
    }
}
