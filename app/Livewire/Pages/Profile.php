<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Enrollment;
use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Mon Profil')]
final class Profile extends Component
{
    public string $search = '';

    public function render(): View
    {
        $user = Auth::user();

        // Statistiques de l'utilisateur
        $totalEnrollments = Enrollment::where('user_id', $user->id)->count();
        $activeEnrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        $completedEnrollments = Enrollment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Formations en cours avec progression
        $myFormations = Enrollment::where('user_id', $user->id)
            ->with(['formation' => function ($query) {
                $query->where('is_active', true);
            }])
            ->whereIn('status', ['active', 'in_progress'])
            ->latest()
            ->take(6)
            ->get();

        // Formations disponibles (non inscrites)
        $availableFormations = Formation::where('is_active', true)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('formation_id')
                    ->from('enrollments')
                    ->where('user_id', $user->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->take(6)
            ->get();

        // Calcul du taux de progression moyen
        $averageProgress = $myFormations->avg(function ($enrollment) {
            return $enrollment->progress_percentage ?? 0;
        });

        return view('livewire.pages.profile', [
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'myFormations' => $myFormations,
            'availableFormations' => $availableFormations,
            'averageProgress' => round((float) ($averageProgress ?? 0), 1),
        ]);
    }
}
