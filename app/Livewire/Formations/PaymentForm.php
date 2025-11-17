<?php

namespace App\Livewire\Formations;

use App\Models\Formation;
use App\Models\FormationAccessCode;
use App\Notifications\FormationAccessCodeNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class PaymentForm extends Component
{
    public Formation $formation;
    public $cardNumber;
    public $expiryDate;
    public $cvv;

    protected $rules = [
        'cardNumber' => 'required|string|min:16|max:16',
        'expiryDate' => 'required|string|size:5',
        'cvv' => 'required|string|size:3',
    ];

    public function mount(Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function processPayment(): void
    {
        $this->validate();

        // Simuler un paiement réussi
        // Dans un vrai projet, intégrer ici le provider de paiement

        // Créer un code d'accès unique
        $accessCode = FormationAccessCode::create([
            'formation_id' => $this->formation->id,
            'code' => Str::random(8),
            'expires_at' => now()->addDays(7),
        ]);

        // Créer l'inscription avec statut en attente
        $enrollment = $this->formation->enrollments()->create([
            'user_id' => Auth::id(),
            'status' => 'active',
            'payment_status' => 'paid',
            'amount_paid' => $this->formation->price,
            'enrollment_date' => now(),
        ]);

        // Envoyer le code par email
        Auth::user()->notify(new FormationAccessCodeNotification($accessCode));

        // Rediriger vers la page de validation du code
        $this->redirect(route('student.formations.validate-code', [
            'formation' => $this->formation,
        ]));
    }

    public function render(): View
    {
        return view('livewire.formations.payment-form');
    }
}
