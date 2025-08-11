<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue Analytics';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        // Monthly revenue for the last 6 months
        $monthlyRevenue = [];
        $monthlyLabels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');

            $revenue = Payment::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');

            $monthlyRevenue[] = $revenue;
        }

        // Revenue by formation
        $formationRevenue = Payment::select(
            'formations.title',
            DB::raw('SUM(payments.amount) as total_revenue'),
            DB::raw('COUNT(payments.id) as payment_count')
        )
            ->join('formations', 'payments.formation_id', '=', 'formations.id')
            ->where('payments.status', 'completed')
            ->groupBy('formations.id', 'formations.title')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $formationLabels = $formationRevenue->pluck('title')->toArray();
        $formationAmounts = $formationRevenue->pluck('total_revenue')->toArray();

        // Calculate total revenue and average transaction value
        $totalRevenue = array_sum($monthlyRevenue);
        $avgTransactionValue = Payment::where('status', 'completed')->avg('amount') ?? 0;

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
