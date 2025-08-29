<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-user')
                ->chart(User::query()
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('DATE(created_at) as date')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(7)
                    ->pluck('count')
                    ->toArray())
                ->color('success'),

            Stat::make('Total Formations', Formation::count())
                ->description('Available courses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart(Formation::query()
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('DATE(created_at) as date')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(7)
                    ->pluck('count')
                    ->toArray())
                ->color('primary'),

            Stat::make('Total Enrollments', Enrollment::count())
                ->description('Course enrollments')
                ->descriptionIcon('heroicon-m-book-open')
                ->chart(Enrollment::query()
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('DATE(created_at) as date')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(7)
                    ->pluck('count')
                    ->toArray())
                ->color('warning'),

            Stat::make('Certificates Issued', Certificate::count())
                ->description('Completed courses')
                ->descriptionIcon('heroicon-m-document-check')
                ->chart(Certificate::query()
                    ->selectRaw('COUNT(*) as count')
                    ->selectRaw('DATE(created_at) as date')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->limit(7)
                    ->pluck('count')
                    ->toArray())
                ->color('info'),
        ];
    }
}
