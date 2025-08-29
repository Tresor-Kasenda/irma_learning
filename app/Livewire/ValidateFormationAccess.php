<?php

namespace App\Livewire;

use App\Models\Formation;
use App\Models\FormationAccessCode;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ValidateFormationAccess extends Component
{
    public Formation $formation;
    public string $accessCode = '';
    public string $error = '';

    public function mount(Formation $formation)
    {
        $this->formation = $formation;
    }

    public function validateCode()
    {
        $this->validate([
            'accessCode' => 'required|string|min:6'
        ]);

        $code = FormationAccessCode::where('formation_id', $this->formation->id)
            ->where('code', $this->accessCode)
            ->where('is_used', false)
            ->first();

        if (!$code || !$code->isValid()) {
            $this->error = 'Code d\'accès invalide ou déjà utilisé';
            return;
        }

        // Marquer le code comme utilisé
        $code->update([
            'is_used' => true,
            'user_id' => Auth::id(),
            'used_at' => now()
        ]);

        // Créer l'inscription
        $this->formation->enrollments()->create([
            'user_id' => Auth::id(),
            'status' => 'active',
            'payment_status' => 'paid',
            'enrollment_date' => now()
        ]);

        $this->redirect(route('student.formation.show', $this->formation));
    }

    public function render()
    {
        return view('livewire.validate-formation-access');
    }
}
