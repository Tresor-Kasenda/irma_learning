<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CertificateStatusEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Services\CatalogStatsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(CatalogStatsService $catalogStats): Response
    {
        $stats = Cache::remember('admin_dashboard_stats', now()->addMinutes(5), function (): array {
            return [
                'formations' => Formation::query()->count(),
                'sections' => Section::query()->count(),
                'exams' => Exam::query()->count(),
                'enrollments' => Enrollment::query()->count(),
                'activeEnrollments' => Enrollment::query()
                    ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID->value, EnrollmentPaymentEnum::FREE->value])
                    ->count(),
                'certificates' => Certificate::query()
                    ->where('status', CertificateStatusEnum::ACTIVE->value)
                    ->count(),
                'students' => User::query()->where('role', UserRoleEnum::STUDENT->value)->count(),
                'revenue' => (float) Enrollment::query()
                    ->where('payment_status', EnrollmentPaymentEnum::PAID->value)
                    ->sum('amount_paid'),
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'catalogStats' => $catalogStats->get(),
            'enrollmentTrends' => [
                'months' => $this->monthlyEnrollmentTrend(),
                'weeks' => $this->weeklyEnrollmentTrend(),
            ],
        ]);
    }

    /**
     * @return array<int, array{label:string, value:int}>
     */
    private function monthlyEnrollmentTrend(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $dates = Enrollment::query()->where('created_at', '>=', $start)->pluck('created_at');

        return collect(range(0, 11))->map(function (int $offset) use ($start, $dates): array {
            $month = $start->copy()->addMonths($offset);

            return [
                'label' => ucfirst($month->translatedFormat('M y')),
                'value' => $dates->filter(fn ($date): bool => Carbon::parse($date)->isSameMonth($month))->count(),
            ];
        })->all();
    }

    /**
     * @return array<int, array{label:string, value:int}>
     */
    private function weeklyEnrollmentTrend(): array
    {
        $start = now()->subWeeks(7)->startOfWeek();
        $dates = Enrollment::query()->where('created_at', '>=', $start)->pluck('created_at');

        return collect(range(0, 7))->map(function (int $offset) use ($start, $dates): array {
            $week = $start->copy()->addWeeks($offset);

            return [
                'label' => 'S'.(int) $week->format('W'),
                'value' => $dates->filter(fn ($date): bool => Carbon::parse($date)->between($week, $week->copy()->endOfWeek()))->count(),
            ];
        })->all();
    }
}
