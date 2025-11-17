<?php

namespace App\Livewire\Formations;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EnrollButton extends Component
{
    public Formation $formation;
    public bool $showPaymentModal = false;

    public function mount(Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function startEnrollment(): void
    {
        // Vérifier si l'utilisateur est déjà inscrit
        if (Auth::user()->isEnrolledIn($this->formation)) {
            $this->redirect(route('student.learning', $this->formation));
            return;
        }

        $this->showPaymentModal = true;
    }

    public function render(): View
    {
        return view('livewire.formations.enroll-button');
    }
}
