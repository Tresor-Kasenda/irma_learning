<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontends;

use App\Enums\ChapterTypeEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Services\CatalogStatsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class FormationsController extends Controller
{
    public function __invoke(Request $request, CatalogStatsService $catalogStatsService)
    {
        $search = mb_trim((string) $request->query('search', ''));
        $category = (string) $request->query('category', 'all');
        $level = (string) $request->query('level', '');
        $contentType = (string) $request->query('content', '');
        $sort = (string) $request->query('sort', 'popular');
        $user = $request->user();

        $allowedCategories = ['all', 'in-progress', 'certified', 'continuous', 'enterprise'];
        $allowedLevels = ['beginner', 'intermediate', 'advanced'];
        $allowedContentTypes = [
            ChapterTypeEnum::VIDEO->value,
            ChapterTypeEnum::PDF->value,
            ChapterTypeEnum::TEXT->value,
        ];
        $allowedSorts = ['popular', 'recent', 'duration-asc', 'duration-desc', 'price-asc'];

        if (! in_array($category, $allowedCategories, true)) {
            $category = 'all';
        }

        if (! in_array($level, $allowedLevels, true)) {
            $level = '';
        }

        if (! in_array($contentType, $allowedContentTypes, true)) {
            $contentType = '';
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'popular';
        }

        $query = Formation::query()
            ->active()
            ->withCount(Formation::catalogCountRelations());

        if ($user) {
            $query->with([
                'enrollments' => fn (HasMany $query): HasMany => $query
                    ->where('user_id', $user->id)
                    ->select(['id', 'formation_id', 'user_id', 'status', 'progress_percentage']),
            ]);
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('short_description', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('tags', 'like', '%'.$search.'%');
            });
        }

        match ($category) {
            'in-progress' => $user
                ? $query->whereHas('enrollments', fn (Builder $query): Builder => $query
                    ->where('user_id', $user->id)
                    ->where('status', EnrollmentStatusEnum::ACTIVE->value)
                    ->whereBetween('progress_percentage', [1, 99]))
                : $query->whereRaw('1 = 0'),
            'certified' => $query->whereHas('exams'),
            'continuous' => $query->where('tags', 'like', '%continue%'),
            'enterprise' => $query->where('tags', 'like', '%entreprise%'),
            default => null,
        };

        if ($level !== '') {
            $query->where('difficulty_level', $level);
        }

        if ($contentType !== '') {
            $query->whereHas('chapters', fn (Builder $query): Builder => $query
                ->where('chapters.is_active', true)
                ->where('content_type', $contentType));
        }

        match ($sort) {
            'recent' => $query->latest(),
            'duration-asc' => $query->orderBy('duration_hours'),
            'duration-desc' => $query->orderByDesc('duration_hours'),
            'price-asc' => $query->orderByRaw('price is not null, price asc'),
            default => $query
                ->orderByDesc('is_featured')
                ->orderByDesc('students_count')
                ->latest(),
        };

        $formations = $query->paginate(8)->withQueryString();

        $catalogStats = $catalogStatsService->get();
        $settings = ApplicationSetting::current();

        $continueLearning = null;

        if ($user) {
            $continueLearning = Enrollment::query()
                ->with([
                    'formation' => fn (BelongsTo $query): BelongsTo => $query->withCount(Formation::catalogCountRelations()),
                ])
                ->where('user_id', $user->id)
                ->where('status', EnrollmentStatusEnum::ACTIVE->value)
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ])
                ->whereHas('formation', fn (Builder $query): Builder => $query->active())
                ->whereBetween('progress_percentage', [1, 99])
                ->latest('updated_at')
                ->first();
        }

        return Inertia::render('Frontends/Formations/Index', [
            'formations' => $formations,
            'catalogStats' => $catalogStats,
            'continueLearning' => $continueLearning,
            'catalogInformation' => [
                'heading' => $settings->catalog_information_heading ?: ApplicationSetting::DEFAULT_CATALOG_INFORMATION_HEADING,
                'items' => $settings->catalog_information_items ?: ApplicationSetting::DEFAULT_CATALOG_INFORMATION_ITEMS,
            ],
            'filters' => [
                'search' => $search,
                'category' => $category,
                'level' => $level,
                'content' => $contentType,
                'sort' => $sort,
            ],
        ]);
    }
}
