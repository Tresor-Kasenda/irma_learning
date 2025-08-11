<?php

namespace App\Filament\Widgets;

use App\Enums\CertificateStatusEnum;
use App\Models\Certificate;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CertificateAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Certificate Issuance Analytics';
    protected static ?int $sort = 8;

    protected function getData(): array
    {
        // Certificate issuance over time (last 6 months)
        $monthlyLabels = [];
        $certificateData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');

            $certificateData[] = Certificate::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Certificate status distribution
        $statusDistribution = [];
        foreach (CertificateStatusEnum::cases() as $status) {
            $statusDistribution[$status->name] = Certificate::where('status', $status->value)->count();
        }

        // Certificates by formation (top 5)
        $certByFormation = Certificate::select(
            'formations.title',
            DB::raw('COUNT(certificates.id) as cert_count')
        )
            ->join('formations', 'certificates.formation_id', '=', 'formations.id')
            ->groupBy('formations.id', 'formations.title')
            ->orderByDesc('cert_count')
            ->limit(5)
            ->get();

        $formationLabels = $certByFormation->pluck('title')->toArray();
        $formationCounts = $certByFormation->pluck('cert_count')->toArray();

        // Calculate completion rate (certificates issued / total enrollments)
        $completionRate = DB::table('enrollments')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN EXISTS (
                    SELECT 1 FROM certificates
                    WHERE certificates.user_id = enrollments.user_id
                    AND certificates.formation_id = enrollments.formation_id
                ) THEN enrollments.id END) as completed,
                COUNT(*) as total
            ')
            ->first();

        $rate = ($completionRate->total > 0)
            ? round(($completionRate->completed / $completionRate->total) * 100, 1)
            : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Certificates Issued',
                    'data' => $certificateData,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Certificates by Formation',
                    'data' => $formationCounts,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(16, 185, 129, 0.6)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(168, 85, 247, 0.6)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_merge($monthlyLabels, $formationLabels),
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => "Course Completion Rate: {$rate}%",
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
