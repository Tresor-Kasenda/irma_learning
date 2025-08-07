<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\MasterClass;
use App\Services\SubscriptionService;
use App\Services\StudentMasterClassService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

final class SubscriptionModal extends Component
{
    public ?MasterClass $masterClass = null;
    public bool $showModal = false;
    public bool $termsAccepted = false;
    public string $modalType = 'subscribe'; // 'subscribe', 'payment', 'info'

    public function render(): View
    {
        return view('livewire.components.subscription-modal');
    }

    #[On('show-subscription-modal')]
    public function showSubscriptionModal(int $masterClassId, string $type = 'subscribe'): void
    {
        $this->masterClass = MasterClass::findOrFail($masterClassId);
        $this->modalType = $type;
        $this->showModal = true;
        $this->termsAccepted = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->masterClass = null;
        $this->termsAccepted = false;
    }

    public function subscribe(): void
    {
        if (!$this->masterClass) {
            return;
        }

        $user = Auth::user();

        if (!$user) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (!$this->termsAccepted) {
            $this->dispatch('notification-add', [
                'type' => 'error',
                'title' => 'Conditions requises',
                'message' => 'Vous devez accepter les conditions d\'utilisation.',
                'duration' => 5000
            ]);
            return;
        }

        try {
            // Si l'utilisateur est un étudiant
            if ($user->isStudent()) {
                $studentService = app(StudentMasterClassService::class);
                $accessInfo = $studentService->canStudentAccessMasterClass($user, $this->masterClass);

                if (!$accessInfo['can_access'] && $accessInfo['reason'] === 'payment_required') {
                    $this->dispatch('notification-add', [
                        'type' => 'warning',
                        'title' => 'Paiement requis',
                        'message' => 'Cette formation nécessite un paiement.',
                        'duration' => 5000
                    ]);
                    $this->modalType = 'payment';
                    return;
                }
            }

            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->subscribeTo($user, $this->masterClass);
            
            $this->dispatch('notification-add', [
                'type' => 'success',
                'title' => 'Inscription réussie !',
                'message' => 'Vous pouvez maintenant accéder à votre formation.',
                'duration' => 5000
            ]);

            $this->closeModal();
            
            // Actualiser la page ou rediriger
            $this->dispatch('subscription-updated');
            
        } catch (\Exception $e) {
            $this->dispatch('notification-add', [
                'type' => 'error',
                'title' => 'Erreur d\'inscription',
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'duration' => 5000
            ]);
        }
    }

    public function startLearning(): void
    {
        if (!$this->masterClass) {
            return;
        }

        $this->closeModal();
        $this->redirect(route('student.course.learning', $this->masterClass), navigate: true);
    }

    public function redirectToPayment(): void
    {
        // Cette méthode sera implémentée avec votre système de paiement
        $this->dispatch('notification-add', [
            'type' => 'info',
            'title' => 'Paiement',
            'message' => 'Redirection vers le paiement (à implémenter).',
            'duration' => 3000
        ]);
        
        $this->closeModal();
    }
}
