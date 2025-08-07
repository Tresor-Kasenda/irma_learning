<?php

declare(strict_types=1);

namespace App\Livewire\Pages\MasterClass;

use App\Models\MasterClass;
use App\Services\SubscriptionService;
use App\Services\StudentMasterClassService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Détails de la Formation')]
final class MasterClassDetails extends Component
{
    #[Locked]
    public MasterClass $masterClass;

    public bool $termsAccepted = false;

    public function mount(MasterClass $masterClass): void
    {
        $this->masterClass = $masterClass->loadMissing(['chapters', 'resources']);
    }

    public function render(): View
    {
        $user = Auth::user();
        $subscription = null;
        $canAccess = false;
        $accessInfo = [];

        if ($user) {
            if ($user->isStudent()) {
                $studentService = app(StudentMasterClassService::class);
                $accessInfo = $studentService->canStudentAccessMasterClass($user, $this->masterClass);
                $canAccess = $accessInfo['can_access'];
                $subscription = $accessInfo['subscription'];
            } else {
                $subscriptionService = app(SubscriptionService::class);
                $subscription = $subscriptionService->getActiveSubscription($user, $this->masterClass);
                $canAccess = $subscriptionService->canAccessMasterClass($user, $this->masterClass);
            }
        }

        return view('livewire.pages.master-class.master-class-details', [
            'subscription' => $subscription,
            'canAccess' => $canAccess,
            'accessInfo' => $accessInfo,
        ]);
    }

    public function subscribe(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (!$this->termsAccepted) {
            $this->dispatch('notify', [
                'message' => 'Vous devez accepter les conditions d\'utilisation.',
                'type' => 'error'
            ]);
            return;
        }

        // Si l'utilisateur est un étudiant, utiliser la logique spécifique
        if ($user->isStudent()) {
            $this->handleStudentSubscription($user);
            return;
        }

        // Pour les autres types d'utilisateurs
        $this->handleGeneralSubscription($user);
    }

    private function handleStudentSubscription($user): void
    {
        $studentService = app(StudentMasterClassService::class);
        $accessInfo = $studentService->canStudentAccessMasterClass($user, $this->masterClass);

        if (!$accessInfo['can_access']) {
            if ($accessInfo['reason'] === 'payment_required') {
                $this->dispatch('notify', [
                    'message' => 'Cette formation est payante. Vous devez effectuer un paiement pour y accéder.',
                    'type' => 'error'
                ]);
                
                // Rediriger vers la page de formations de l'étudiant
                $this->redirect(route('student.my-master-classes', ['activeTab' => 'available']), navigate: true);
                return;
            }
        }

        // Si c'est gratuit ou déjà accessible
        $this->processSubscription($user);
    }

    private function handleGeneralSubscription($user): void
    {
        $subscriptionService = app(SubscriptionService::class);
        
        if (!$subscriptionService->canAccessMasterClass($user, $this->masterClass)) {
            $this->dispatch('notify', [
                'message' => 'Vous devez payer pour accéder à cette formation.',
                'type' => 'error'
            ]);
            return;
        }

        $this->processSubscription($user);
    }

    private function processSubscription($user): void
    {
        try {
            $subscriptionService = app(SubscriptionService::class);
            $subscription = $subscriptionService->subscribeTo($user, $this->masterClass);
            
            $this->dispatch('notify', [
                'message' => 'Inscription réussie ! Vous pouvez maintenant commencer votre formation.',
                'type' => 'success'
            ]);
            
            // Rediriger vers la page d'apprentissage
            $this->redirect(route('student.course.learning', $this->masterClass), navigate: true);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'type' => 'error'
            ]);
        }
    }

    public function startLearning(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $this->redirect(route('student.course.learning', $this->masterClass), navigate: true);
    }

    public function redirectToPayment(): void
    {
        // Cette méthode sera implémentée quand vous ajouterez le système de paiement
        $this->dispatch('notify', [
            'message' => 'Redirection vers le système de paiement (à implémenter).',
            'type' => 'info'
        ]);
    }
}
