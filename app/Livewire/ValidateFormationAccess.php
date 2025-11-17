<?php

namespace App\Livewire;

use App\Models\Formation;
use App\Models\FormationAccessCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ValidateFormationAccess extends Component
{
    public Formation $formation;
    public string $accessCode = '';
    public string $error = '';

    public function mount(Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function validateCode(): void
    {
        $this->validate([
            'accessCode' => 'required|string|min:6'
        ]);

        $code = FormationAccessCode::query()
            ->whereBelongsTo($this->formation)
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

    public function render(): View
    {
        return view('livewire.validate-formation-access');
    }
}
