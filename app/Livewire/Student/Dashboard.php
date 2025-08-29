<?php

namespace App\Livewire\Student;

use App\Models\Formation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function activeFormations()
    {
        return Auth::user()
            ->formations()
            ->with(['modules', 'modules.sections'])
            ->whereHas('enrollments', function ($query) {
                $query->where('status', 'active')
                    ->where('payment_status', 'paid');
            })
            ->get();
    }

    #[Computed]
    public function completedFormations()
    {
        return Auth::user()
            ->formations()
            ->with(['modules', 'modules.sections'])
            ->whereHas('enrollments', function ($query) {
                $query->where('status', 'completed');
            })
            ->get();
    }

    #[Computed]
    public function stats()
    {
        $user = Auth::user();
        
        return [
            'total_formations' => $user->formations()->count(),
            'completed_formations' => $this->completedFormations->count(),
            'total_modules_completed' => $user->formations->sum(function (Formation $formation) {
                return $formation->getCompletedModulesCount($user);
            }),
            'total_sections_completed' => $user->formations->sum(function (Formation $formation) {
                return $formation->getCompletedSectionsCount($user);
            }),
            'certificates_earned' => $user->certificates()->count(),
            'average_score' => round($user->examAttempts()->avg('percentage') ?? 0, 1),
        ];
    }

    public function render()
    {
        return view('livewire.student.dashboard')
            ->layout('layouts.app');
    }
}
