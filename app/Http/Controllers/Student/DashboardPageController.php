<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\CertificateStatusEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\UserProgress;
use App\Services\CatalogStatsService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

final class DashboardPageController extends Controller
{
    public function __invoke(CatalogStatsService $catalogStats)
    {

        $user = auth()->user();
        $catalogCountRelations = Formation::catalogCountRelations();

        $myEnrollments = Enrollment::query()
            ->with([
                'formation' => fn (BelongsTo $query): BelongsTo => $query
                    ->withCount($catalogCountRelations),
            ])
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatusEnum::ACTIVE->value)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->latest('updated_at')
            ->get();

        $continueWatching = UserProgress::query()
            ->with(['trackable' => function ($query) use ($catalogCountRelations) {
                $query->with([
                    'section.formation' => fn (BelongsTo $query): BelongsTo => $query
                        ->withCount($catalogCountRelations),
                ]);
            }])
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->where('status', UserProgressEnum::IN_PROGRESS->value)
            ->latest('updated_at')
            ->first();

        $search = request()->query('q');

        $recommendedFormations = Formation::query()
            ->withCount($catalogCountRelations)
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('formation_id')
                    ->from('enrollments')
                    ->where('user_id', $user->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->limit(8)
            ->latest()
            ->get();

        $recentFormations = Formation::query()
            ->withCount($catalogCountRelations)
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();

        $completedCertificates = Certificate::query()
            ->where('user_id', $user->id)
            ->where('status', CertificateStatusEnum::ACTIVE->value)
            ->get(['id', 'formation_id', 'certificate_number'])
            ->keyBy('formation_id')
            ->map(fn (Certificate $certificate): array => [
                'id' => $certificate->id,
                'certificate_number' => $certificate->certificate_number,
            ]);

        $stats = [
            'totalEnrollments' => Enrollment::where('user_id', $user->id)->count(),
            'activeEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', EnrollmentStatusEnum::ACTIVE->value)
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ])
                ->count(),
            'completedEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', EnrollmentStatusEnum::COMPLETED->value)
                ->count(),
            'averageProgress' => (int) Enrollment::where('user_id', $user->id)
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ])
                ->avg('progress_percentage'),
            'totalTimeSpent' => UserProgress::where('user_id', $user->id)->sum('time_spent'),
            'certificatesEarned' => DB::table('certificates')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
        ];

        $popularCategories = [
            ['name' => 'Développement Web', 'count' => Formation::where('is_active', true)->count()],
            ['name' => 'Design', 'count' => 0],
            ['name' => 'Business', 'count' => 0],
            ['name' => 'Marketing', 'count' => 0],
        ];

        return Inertia::render('Student/Index', [
            'myEnrollments' => $myEnrollments,
            'continueWatching' => $continueWatching,
            'recommendedFormations' => $recommendedFormations,
            'recentFormations' => $recentFormations,
            'completedCertificates' => $completedCertificates,
            'stats' => $stats,
            'catalogStats' => $catalogStats->get(),
            'popularCategories' => $popularCategories,
            'search' => $search,
        ]);
    }
}
