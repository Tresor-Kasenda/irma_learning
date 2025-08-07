<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Student;

use App\Models\MasterClass;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Mes Formations')]
final class MyMasterClasses extends Component
{
    use WithPagination;

    public string $activeTab = 'subscribed';
    public string $search = '';

    protected $queryString = [
        'activeTab' => ['except' => 'subscribed'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render(): View
    {
        $user = Auth::user();
        $studentService = app(\App\Services\StudentMasterClassService::class);

        // Master classes auxquelles l'utilisateur est souscrit
        $subscribedMasterClasses = collect();
        if ($this->activeTab === 'subscribed' || $this->activeTab === 'all') {
            $subscribedMasterClasses = $studentService
                ->getSubscribedMasterClasses($user, $this->search)
                ->paginate(6);
        }

        // Master classes disponibles (non souscrites)
        $availableMasterClasses = collect();
        if ($this->activeTab === 'available' || $this->activeTab === 'all') {
            $availableMasterClasses = $studentService
                ->getAvailableMasterClasses($user, $this->search)
                ->paginate(6);
        }

        // Master classes payées
        $paidMasterClasses = collect();
        if ($this->activeTab === 'paid' || $this->activeTab === 'all') {
            $paidMasterClasses = $studentService
                ->getPaidMasterClasses($user, $this->search)
                ->paginate(6);
        }

        return view('livewire.pages.student.my-master-classes', [
            'subscribedMasterClasses' => $subscribedMasterClasses,
            'availableMasterClasses' => $availableMasterClasses,
            'paidMasterClasses' => $paidMasterClasses,
            'studentStats' => $studentService->getStudentStats($user),
        ]);
    }

    public function subscribe(int $masterClassId): void
    {
        $masterClass = MasterClass::findOrFail($masterClassId);
        $user = Auth::user();
        
        $subscriptionService = app(SubscriptionService::class);
        
        // Vérifier si la master class est gratuite
        if (!$masterClass->isFree()) {
            $this->dispatch('show-subscription-modal', $masterClassId, 'payment');
            return;
        }

        try {
            $subscriptionService->subscribeTo($user, $masterClass);
            
            $this->dispatch('notify', [
                'message' => 'Inscription réussie ! Vous pouvez maintenant accéder à votre formation gratuite.',
                'type' => 'success'
            ]);
            
            // Actualiser la page pour refléter les changements
            $this->redirect(route('student.my-master-classes'), navigate: true);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'type' => 'error'
            ]);
        }
    }

    public function viewMasterClass(int $masterClassId): void
    {
        $this->redirect(route('master-class', ['masterClass' => $masterClassId]), navigate: true);
    }

    public function startLearning(int $masterClassId): void
    {
        $this->redirect(route('student.course.learning', ['masterClass' => $masterClassId]), navigate: true);
    }
}
