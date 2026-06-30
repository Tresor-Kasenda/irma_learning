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
        ]);
    }
}
