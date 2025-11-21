<?php

namespace App\Filament\Widgets;

use App\Enums\UserRoleEnum;
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
        $user = User::query()->where('role', '=', UserRoleEnum::STUDENT->value);
        $formations = Formation::query();
        $enrollents = Enrollment::query();
        $certificates = Certificate::query();
        return [
            Stat::make(
                'Total Users',
                $user->count()
            )
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-user')
                ->chart(
                    $user
                        ->selectRaw('COUNT(*) as count')
                        ->selectRaw('DATE(created_at) as date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->limit(7)
                        ->pluck('count')
                        ->toArray()
                )
                ->color('success'),

            Stat::make('Total Formations', $formations->count())
                ->description('Available courses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart(
                    $formations
                        ->selectRaw('COUNT(*) as count')
                        ->selectRaw('DATE(created_at) as date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->limit(7)
                        ->pluck('count')
                        ->toArray()
                )
                ->color('primary'),

            Stat::make('Total Enrollments', $enrollents->count())
                ->description('Course enrollments')
                ->descriptionIcon('heroicon-m-book-open')
                ->chart(
                    $enrollents
                        ->selectRaw('COUNT(*) as count')
                        ->selectRaw('DATE(created_at) as date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->limit(7)
                        ->pluck('count')
                        ->toArray()
                )
                ->color('warning'),

            Stat::make('Certificates Issued', $certificates->count())
                ->description('Completed courses')
                ->descriptionIcon('heroicon-m-document-check')
                ->chart(
                    $certificates
                        ->selectRaw('COUNT(*) as count')
                        ->selectRaw('DATE(created_at) as date')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->limit(7)
                        ->pluck('count')
                        ->toArray()
                )
                ->color('info'),
        ];
    }
}
