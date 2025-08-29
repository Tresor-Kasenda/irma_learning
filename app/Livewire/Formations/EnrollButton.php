<?php

namespace App\Livewire\Formations;

use App\Models\Formation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EnrollButton extends Component
{
    public Formation $formation;
    public bool $showPaymentModal = false;

    public function mount(Formation $formation)
    {
        $this->formation = $formation;
    }

    public function startEnrollment()
    {
        // Vérifier si l'utilisateur est déjà inscrit
        if (Auth::user()->isEnrolledIn($this->formation)) {
            $this->redirect(route('student.learning', $this->formation));
            return;
        }

        $this->showPaymentModal = true;
    }

    public function render()
    {
        return view('livewire.formations.enroll-button');
    }
}
