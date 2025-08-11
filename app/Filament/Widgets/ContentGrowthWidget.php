<?php

namespace App\Filament\Widgets;

use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Section;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ContentGrowthWidget extends ChartWidget
{
    protected static ?string $heading = 'Content Growth Analytics';
    protected static ?int $sort = 7;

    protected function getData(): array
    {
        // Monthly content creation for the last 6 months
        $monthlyLabels = [];
        $formationData = [];
        $moduleData = [];
        $sectionData = [];
        $chapterData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');

            // Count formations created each month
            $formationData[] = Formation::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            // Count modules created each month
            $moduleData[] = Module::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            // Count sections created each month
            $sectionData[] = Section::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            // Count chapters created each month
            $chapterData[] = Chapter::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Calculate content composition
        $totalFormations = Formation::count();
        $totalModules = Module::count();
        $totalSections = Section::count();
        $totalChapters = Chapter::count();

        // Average content metrics
        $avgModulesPerFormation = $totalFormations > 0 ? round($totalModules / $totalFormations, 1) : 0;
        $avgSectionsPerModule = $totalModules > 0 ? round($totalSections / $totalModules, 1) : 0;
        $avgChaptersPerSection = $totalSections > 0 ? round($totalChapters / $totalSections, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Formations',
                    'data' => $formationData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Modules',
                    'data' => $moduleData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.6)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Sections',
                    'data' => $sectionData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Chapters',
                    'data' => $chapterData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.6)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $monthlyLabels,
            'options' => [
                'plugins' => [
                    'tooltip' => [
                        'callbacks' => [
                            'footer' => "function() {
                                return 'Avg: ' + {$avgModulesPerFormation} + ' modules per formation | ' +
                                       {$avgSectionsPerModule} + ' sections per module | ' +
                                       {$avgChaptersPerSection} + ' chapters per section';
                            }"
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
