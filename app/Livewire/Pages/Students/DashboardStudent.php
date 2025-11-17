<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Students;

use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\UserProgress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

final class DashboardStudent extends Component
{
    public string $search = '';

    public function mount(): void
    {
        // Initialisation si nécessaire
    }

    public function render(): View
    {
        $user = auth()->user();

        // Mes formations en cours avec progression
        $myEnrollments = Enrollment::query()
            ->with(['formation' => function ($query) {
                $query->with(['modules.sections.chapters']);
            }])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('payment_status', ['completed', 'free'])
            ->latest('updated_at')
            ->get();

        // Dernière formation consultée (celle avec l'activité la plus récente)
        $continueWatching = UserProgress::query()
            ->with(['trackable' => function ($query) {
                $query->with(['section.module.formation']);
            }])
            ->where('user_id', $user->id)
            ->where('trackable_type', 'App\Models\Chapter')
            ->where('status', 'in_progress')
            ->latest('updated_at')
            ->first();

        // Formations recommandées (non inscrites)
        $recommendedFormations = Formation::query()
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('formation_id')
                    ->from('enrollments')
                    ->where('user_id', $user->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('short_description', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->limit(8)
            ->latest()
            ->get();

        // Statistiques de l'utilisateur
        $stats = [
            'totalEnrollments' => Enrollment::where('user_id', $user->id)->count(),
            'activeEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereIn('payment_status', ['completed', 'free'])
                ->count(),
            'completedEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'averageProgress' => (int) Enrollment::where('user_id', $user->id)
                ->whereIn('payment_status', ['completed', 'free'])
                ->avg('progress_percentage'),
            'totalTimeSpent' => UserProgress::where('user_id', $user->id)->sum('time_spent'),
            'certificatesEarned' => DB::table('certificates')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
        ];

        // Catégories populaires (basées sur les formations disponibles)
        $popularCategories = [
            ['name' => 'Développement Web', 'count' => Formation::where('is_active', true)->count()],
            ['name' => 'Design', 'count' => 0],
            ['name' => 'Business', 'count' => 0],
            ['name' => 'Marketing', 'count' => 0],
        ];

        return view('livewire.pages.students.dashboard-student', [
            'myEnrollments' => $myEnrollments,
            'continueWatching' => $continueWatching,
            'recommendedFormations' => $recommendedFormations,
            'stats' => $stats,
            'popularCategories' => $popularCategories,
        ]);
    }
}
