<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Formations;

use App\Enums\ChapterTypeEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class StudentFormationController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $tab = (string) $request->query('tab', 'recent');
        $search = mb_trim((string) $request->query('search', ''));
        $level = (string) $request->query('level', '');
        $contentType = (string) $request->query('content', '');

        $allowedTabs = ['recent', 'discover', 'started', 'completed'];
        $allowedLevels = ['beginner', 'intermediate', 'advanced'];
        $allowedContentTypes = [
            ChapterTypeEnum::VIDEO->value,
            ChapterTypeEnum::PDF->value,
            ChapterTypeEnum::TEXT->value,
        ];
        $allowedSorts = [
            'last-interacted',
            'popular',
            'recent',
            'title',
            'duration-asc',
            'duration-desc',
        ];

        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'recent';
        }

        if (! in_array($level, $allowedLevels, true)) {
            $level = '';
        }

        if (! in_array($contentType, $allowedContentTypes, true)) {
            $contentType = '';
        }

        $defaultSort = $tab === 'discover' ? 'popular' : 'last-interacted';
        $sort = (string) $request->query('sort', $defaultSort);

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        if ($tab === 'discover' && $sort === 'last-interacted') {
            $sort = 'popular';
        }

        $formations = $this->queryForTab($tab, $user->id)
            ->withCount(Formation::catalogCountRelations())
            ->with([
                'enrollments' => fn (HasMany $query): HasMany => $query
                    ->where('user_id', $user->id)
                    ->whereIn('payment_status', $this->accessiblePaymentStatuses())
                    ->latest('updated_at')
                    ->select([
                        'id',
                        'formation_id',
                        'user_id',
                        'status',
                        'payment_status',
                        'progress_percentage',
                        'last_accessed_at',
                        'updated_at',
                    ]),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('tags', 'like', '%'.$search.'%');
                });
            })
            ->when($level !== '', fn (Builder $query): Builder => $query
                ->where('difficulty_level', $level))
            ->when($contentType !== '', fn (Builder $query): Builder => $query
                ->whereHas('chapters', fn (Builder $query): Builder => $query
                    ->where('chapters.is_active', true)
                    ->where('content_type', $contentType)));

        $this->applySort($formations, $sort, $tab, $user->id);

        $activeChapters = Chapter::query()
            ->where('chapters.is_active', true)
            ->whereHas('section.formation', fn (Builder $query): Builder => $query->active());

        return Inertia::render('Dashboard/Formations/Index', [
            'formations' => $formations->paginate(9)->withQueryString(),
            'tabCounts' => [
                'recent' => $this->queryForTab('recent', $user->id)->count(),
                'discover' => $this->queryForTab('discover', $user->id)->count(),
                'started' => $this->queryForTab('started', $user->id)->count(),
                'completed' => $this->queryForTab('completed', $user->id)->count(),
            ],
            'catalogStats' => [
                'formations' => Formation::query()->active()->count(),
                'videos' => (clone $activeChapters)
                    ->where('content_type', ChapterTypeEnum::VIDEO->value)
                    ->count(),
                'pdfs' => (clone $activeChapters)
                    ->where('content_type', ChapterTypeEnum::PDF->value)
                    ->count(),
                'texts' => (clone $activeChapters)
                    ->where('content_type', ChapterTypeEnum::TEXT->value)
                    ->count(),
            ],
            'filters' => [
                'tab' => $tab,
                'search' => $search,
                'level' => $level,
                'content' => $contentType,
                'sort' => $sort,
            ],
        ]);
    }

    private function queryForTab(string $tab, int $userId): Builder
    {
        $query = Formation::query()->active();

        return match ($tab) {
            'discover' => $query->whereDoesntHave(
                'enrollments',
                fn (Builder $query): Builder => $query->where('user_id', $userId),
            ),
            'started' => $query->whereHas(
                'enrollments',
                fn (Builder $query): Builder => $query
                    ->where('user_id', $userId)
                    ->where('status', EnrollmentStatusEnum::ACTIVE->value)
                    ->whereIn('payment_status', $this->accessiblePaymentStatuses())
                    ->whereBetween('progress_percentage', [1, 99.99]),
            ),
            'completed' => $query->whereHas(
                'enrollments',
                fn (Builder $query): Builder => $query
                    ->where('user_id', $userId)
                    ->whereIn('payment_status', $this->accessiblePaymentStatuses())
                    ->where(function (Builder $query): void {
                        $query->where('status', EnrollmentStatusEnum::COMPLETED->value)
                            ->orWhere('progress_percentage', '>=', 100);
                    }),
            ),
            default => $query->whereHas(
                'enrollments',
                fn (Builder $query): Builder => $query
                    ->where('user_id', $userId)
                    ->whereIn('status', [
                        EnrollmentStatusEnum::ACTIVE->value,
                        EnrollmentStatusEnum::COMPLETED->value,
                    ])
                    ->whereIn('payment_status', $this->accessiblePaymentStatuses()),
            ),
        };
    }

    private function applySort(Builder $query, string $sort, string $tab, int $userId): void
    {
        match ($sort) {
            'popular' => $query
                ->orderByDesc('is_featured')
                ->orderByDesc('students_count')
                ->latest('formations.created_at'),
            'recent' => $query->latest('formations.created_at'),
            'title' => $query->orderBy('title'),
            'duration-asc' => $query->orderBy('duration_hours'),
            'duration-desc' => $query->orderByDesc('duration_hours'),
            default => $tab === 'discover'
                ? $query
                    ->orderByDesc('is_featured')
                    ->orderByDesc('students_count')
                    ->latest('formations.created_at')
                : $query
                    ->orderByDesc(
                        Enrollment::query()
                            ->selectRaw('coalesce(last_accessed_at, updated_at)')
                            ->whereColumn('formation_id', 'formations.id')
                            ->where('user_id', $userId)
                            ->whereIn('payment_status', $this->accessiblePaymentStatuses())
                            ->latest('updated_at')
                            ->limit(1),
                    )
                    ->latest('formations.created_at'),
        };
    }

    /**
     * @return list<string>
     */
    private function accessiblePaymentStatuses(): array
    {
        return [
            EnrollmentPaymentEnum::PAID->value,
            EnrollmentPaymentEnum::FREE->value,
        ];
    }
}
