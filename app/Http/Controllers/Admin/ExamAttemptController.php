<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ExamAttemptController extends Controller
{
    private const array SORTABLE = ['id', 'attempt_number', 'percentage', 'score', 'started_at', 'completed_at'];

    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request): Response
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'started_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 10;

        $attempts = ExamAttempt::query()
            ->with(['user:id,name,email', 'exam:id,title,passing_score'])
            ->when($request->query('search'), fn (Builder $query, string $search): Builder => $query
                ->whereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->when($request->filled('exam_id'), fn (Builder $query): Builder => $query
                ->where('exam_id', $request->integer('exam_id')))
            ->when($request->filled('status'), fn (Builder $query): Builder => $query
                ->where('status', $request->query('status')))
            ->when($request->filled('is_passed'), function (Builder $query) use ($request): void {
                if ($request->boolean('is_passed')) {
                    $query->where('status', 'completed')->whereColumn('percentage', '>=', 'exams.passing_score');
                } else {
                    $query->where(fn (Builder $q) => $q
                        ->where('status', '!=', 'completed')
                        ->orWhereColumn('percentage', '<', 'exams.passing_score'));
                }
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Attempts/Index', [
            'attempts' => $attempts,
            'filters' => $request->only('search', 'exam_id', 'status', 'is_passed', 'sort', 'dir', 'per_page'),
        ]);
    }

    public function show(ExamAttempt $attempt): Response
    {
        $attempt->load([
            'user:id,name,email',
            'exam:id,title,passing_score',
            'userAnswers' => fn ($q) => $q->with(['question:id,question_text,question_type,points', 'selectedOption:id,option_text']),
        ]);

        return Inertia::render('Admin/Attempts/Show', [
            'attempt' => [
                'id' => $attempt->id,
                'user' => $attempt->user ? ['id' => $attempt->user->id, 'name' => $attempt->user->name, 'email' => $attempt->user->email] : null,
                'exam' => $attempt->exam ? ['id' => $attempt->exam->id, 'title' => $attempt->exam->title, 'passing_score' => $attempt->exam->passing_score] : null,
                'attempt_number' => $attempt->attempt_number,
                'status' => $attempt->status->value,
                'score' => $attempt->score,
                'max_score' => $attempt->max_score,
                'percentage' => $attempt->percentage,
                'time_taken' => $attempt->time_taken,
                'started_at' => $attempt->started_at?->toISOString(),
                'completed_at' => $attempt->completed_at?->toISOString(),
                'expires_at' => $attempt->expires_at?->toISOString(),
                'reopened_at' => $attempt->reopened_at?->toISOString(),
                'reopen_count' => $attempt->reopen_count,
                'can_reopen' => in_array($attempt->status->value, ['expired', 'failed', 'cancelled'], true),
                'answers' => $attempt->userAnswers->map(fn ($a): array => [
                    'id' => $a->id,
                    'question_text' => $a->question?->question_text ?? 'Question supprimée',
                    'question_type' => $a->question?->question_type?->value ?? 'unknown',
                    'selected_option_text' => $a->selectedOption?->option_text
                        ?? ($a->selected_options ? implode(', ', $a->selected_options) : '—'),
                    'answer_text' => $a->answer_text,
                    'is_correct' => $a->is_correct,
                    'points_earned' => $a->points_earned,
                    'max_points' => $a->question?->points ?? 0,
                    'feedback' => $a->feedback,
                ]),
            ],
        ]);
    }

    public function complete(ExamAttempt $attempt): RedirectResponse
    {
        if ($attempt->status->value === 'completed' || $attempt->status->value === 'failed') {
            return back()->with('error', 'Cette tentative est déjà terminée.');
        }

        $attempt->complete();

        return back()->with('success', 'Tentative complétée.');
    }

    public function reopen(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        if (! $attempt->reopen($request->user())) {
            return back()->with('error', 'Seules les tentatives expirées, échouées ou annulées peuvent être réouvertes.');
        }

        return back()->with('success', 'Tentative réouverte. L’apprenant peut reprendre son évaluation.');
    }
}
