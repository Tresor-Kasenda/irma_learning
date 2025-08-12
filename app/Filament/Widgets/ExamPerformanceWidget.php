<?php

namespace App\Filament\Widgets;

use App\Enums\ExamResultEnum;
use App\Models\ExamAttempt;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExamPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Exam Performance Analytics';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $resultDistribution = [];
        foreach (ExamResultEnum::cases() as $result) {
            $resultDistribution[$result->name] = ExamAttempt::where('result', $result->value)->count();
        }

        $examScores = ExamAttempt::select(
            'exams.id',
            'exams.title',
            DB::raw('AVG(exam_attempts.score) as average_score'),
            DB::raw('COUNT(exam_attempts.id) as attempt_count')
        )
            ->join('exams', 'exam_attempts.exam_id', '=', 'exams.id')
            ->groupBy('exams.id', 'exams.title')
            ->orderByDesc('attempt_count')
            ->limit(5)
            ->get();

        $examLabels = $examScores->pluck('title')->toArray();
        $examAverages = $examScores->pluck('average_score')->toArray();
        $examAttempts = $examScores->pluck('attempt_count')->toArray();

        $passCount = ExamAttempt::where('result', ExamResultEnum::PASSED->value)->count();
        $failCount = ExamAttempt::where('result', ExamResultEnum::FAILED->value)->count();
        $totalAttempts = $passCount + $failCount;
        $passRate = $totalAttempts > 0 ? round(($passCount / $totalAttempts) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Average Score (%)',
                    'data' => $examAverages,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Attempt Count',
                    'data' => $examAttempts,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $examLabels,
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'max' => 100,
                        'title' => [
                            'display' => true,
                            'text' => 'Score Percentage'
                        ]
                    ],
                    'y1' => [
                        'position' => 'right',
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Number of Attempts'
                        ]
                    ]
                ],
                'plugins' => [
                    'legend' => [
                        'display' => true,
                    ],
                    'tooltip' => [
                        'enabled' => true,
                    ],
                    'datalabels' => [
                        'display' => true,
                        'color' => '#fff',
                        'font' => [
                            'weight' => 'bold',
                        ],
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
