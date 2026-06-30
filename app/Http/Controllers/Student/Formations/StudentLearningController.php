<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Formations;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Services\CatalogStatsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class StudentLearningController extends Controller
{
    public function __invoke(Request $request, CatalogStatsService $catalogStats): Response
    {
        $user = $request->user();

        $allowedSorts = ['recent', 'progress-desc', 'progress-asc', 'title'];
        $sort = (string) $request->query('sort', 'recent');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'recent';
        }

        $formations = Formation::query()
            ->active()
            ->withCount(Formation::catalogCountRelations())
            ->whereHas('enrollments', fn (Builder $query): Builder => $this
                ->inProgressEnrollment($query, $user->id))
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
            ]);

        $this->applySort($formations, $sort, $user->id);

        $courses = $formations->get();

        $progressValues = $courses->map(
            fn (Formation $formation): float => (float) ($formation->enrollments->first()?->progress_percentage ?? 0),
        );

        return Inertia::render('Student/Formations/Learnings/Index', [
            'courses' => $courses,
            'stats' => [
                'inProgress' => $courses->count(),
                'averageProgress' => $progressValues->isNotEmpty()
                    ? (int) round($progressValues->avg())
                    : 0,
                'completed' => $this->completedEnrollmentsQuery($user->id)->count(),
            ],
            'catalogStats' => $catalogStats->get(),
            'filters' => [
                'sort' => $sort,
            ],
        ]);
    }

    private function inProgressEnrollment(Builder $query, int $userId): Builder
    {
        return $query
            ->where('user_id', $userId)
            ->where('status', EnrollmentStatusEnum::ACTIVE->value)
            ->whereIn('payment_status', $this->accessiblePaymentStatuses())
            ->where('progress_percentage', '<', 100);
    }

    private function completedEnrollmentsQuery(int $userId): Builder
    {
        return Enrollment::query()
            ->where('user_id', $userId)
            ->whereIn('payment_status', $this->accessiblePaymentStatuses())
            ->where(function (Builder $query): void {
                $query->where('status', EnrollmentStatusEnum::COMPLETED->value)
                    ->orWhere('progress_percentage', '>=', 100);
            });
    }

    private function applySort(Builder $query, string $sort, int $userId): void
    {
        $latestEnrollment = fn (string $column): Builder => Enrollment::query()
            ->select($column)
            ->whereColumn('formation_id', 'formations.id')
            ->where('user_id', $userId)
            ->whereIn('payment_status', $this->accessiblePaymentStatuses())
            ->latest('updated_at')
            ->limit(1);

        match ($sort) {
            'progress-desc' => $query->orderByDesc($latestEnrollment('progress_percentage')),
            'progress-asc' => $query->orderBy($latestEnrollment('progress_percentage')),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc(
                Enrollment::query()
                    ->selectRaw('coalesce(last_accessed_at, updated_at)')
                    ->whereColumn('formation_id', 'formations.id')
                    ->where('user_id', $userId)
                    ->whereIn('payment_status', $this->accessiblePaymentStatuses())
                    ->latest('updated_at')
                    ->limit(1),
            ),
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
