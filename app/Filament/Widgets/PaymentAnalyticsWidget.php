<?php

namespace App\Filament\Widgets;

use App\Enums\EnrollmentPaymentEnum;
use App\Models\Enrollment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Analytics';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $monthlyRevenue = [];
        $monthlyLabels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');

            $revenue = Enrollment::query()
                ->where('payment_status', '=', EnrollmentPaymentEnum::PAID)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount_paid');

            $monthlyRevenue[] = $revenue;
        }

        $formationRevenue = Enrollment::select(
            'formations.title',
            DB::raw('SUM(enrollments.amount_paid) as total_revenue'),
            DB::raw('COUNT(enrollments.id) as payment_count')
        )
            ->join('formations', 'enrollments.formation_id', '=', 'formations.id')
            ->where('enrollments.payment_status', '=', EnrollmentPaymentEnum::PAID)
            ->groupBy('formations.id', 'formations.title')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $formationLabels = $formationRevenue->pluck('title')->toArray();
        $formationAmounts = $formationRevenue->pluck('total_revenue')->toArray();

        $totalRevenue = array_sum($monthlyRevenue);
        $avgTransactionValue = Enrollment::query()
            ->where('payment_status', '=', EnrollmentPaymentEnum::PAID)->avg('amount') ?? 0;

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Revenue (€)',
                    'data' => $monthlyRevenue,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Top Formations by Revenue (€)',
                    'data' => $formationAmounts,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                        'rgba(236, 72, 153, 0.6)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)',
                        'rgb(236, 72, 153)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_merge($monthlyLabels, $formationLabels),
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => "Total Revenue: €{$totalRevenue} | Avg Transaction: €" . number_format($avgTransactionValue, 2),
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
