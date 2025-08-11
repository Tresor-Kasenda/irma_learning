<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class EnrollmentStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Enrollment Analytics';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        // Enrollment status breakdown
        $enrollmentStatusData = [];
        foreach (EnrollmentStatusEnum::cases() as $status) {
            $enrollmentStatusData[$status->name] = Enrollment::where('status', $status->value)->count();
        }

        // Payment status breakdown
        $paymentStatusData = [];
        foreach (EnrollmentPaymentEnum::cases() as $status) {
            $paymentStatusData[$status->name] = Enrollment::where('payment_status', $status->value)->count();
        }

        // Top 5 formations by enrollment count
        $topFormations = Formation::withCount('students')
            ->orderByDesc('students_count')
            ->limit(5)
            ->get()
            ->pluck('students_count', 'title')
            ->toArray();

        // Monthly enrollment growth (last 6 months)
        $monthlyData = [];
        $monthlyLabels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlyData[] = Enrollment::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Enrollments',
                    'data' => $monthlyData,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1,
                    'type' => 'line',
                ],
                [
                    'label' => 'Enrollment Status',
                    'data' => array_values($enrollmentStatusData),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)', // In Progress
                        'rgba(16, 185, 129, 0.6)', // Completed
                        'rgba(245, 158, 11, 0.6)', // Pending
                        'rgba(239, 68, 68, 0.6)',  // Dropped
                    ],
                    'type' => 'pie',
                ],
            ],
            'labels' => array_merge($monthlyLabels, array_keys($enrollmentStatusData)),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
