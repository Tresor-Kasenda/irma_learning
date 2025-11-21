<?php

namespace App\Filament\Widgets;

use App\Enums\FormationLevelEnum;
use App\Models\Formation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FormationStatsChart extends ChartWidget
{
    protected static ?string $heading = 'Formations Statistics';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $formations = Formation::query();
        $formationsByLevel = $formations
            ->select('difficulty_level', DB::raw('count(*) as total'))
            ->groupBy('difficulty_level')
            ->pluck('total', 'difficulty_level')
            ->toArray();

        $activeFormations = $formations
            ->where('is_active', '=', true)
            ->count();
        $inactiveFormations = $formations
            ->where('is_active', '=', false)
            ->count();
        $featuredFormations = $formations
            ->where('is_featured', '=', true)
            ->count();

        $labels = [];
        $data = [];

        foreach (FormationLevelEnum::cases() as $level) {
            $labels[] = $level->name;
            $data[] = $formationsByLevel[$level->value] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Formations by Difficulty Level',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.6)', // Beginner
                        'rgba(255, 206, 86, 0.6)', // Intermediate
                        'rgba(255, 99, 132, 0.6)', // Advanced
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)',
                    ],
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Formation Status',
                    'data' => [$activeFormations, $inactiveFormations, $featuredFormations],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                    ],
                    'borderColor' => [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => array_merge($labels, ['Active', 'Inactive', 'Featured']),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
