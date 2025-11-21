<?php

namespace App\Filament\Widgets;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class UserActivityChart extends ChartWidget
{
    protected static ?string $heading = 'User Activity Analytics';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $range = [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()];

        $user = User::query();

        $newUsers = $user
            ->where('role', '=', UserRoleEnum::STUDENT->value)
            ->whereBetween('created_at', $range)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $progressEntries = $user
            ->where('role', '=', UserRoleEnum::STUDENT->value)
            ->whereBetween('created_at', $range)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $examAttempts = $user
            ->where('role', '=', UserRoleEnum::STUDENT->value)
            ->whereBetween('created_at', $range)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $userData = [];
        $progressData = [];
        $examData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $userData[] = $newUsers[$date] ?? 0;
            $progressData[] = $progressEntries[$date] ?? 0;
            $examData[] = $examAttempts[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $userData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Learning Activities',
                    'data' => $progressData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Exam Attempts',
                    'data' => $examData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
