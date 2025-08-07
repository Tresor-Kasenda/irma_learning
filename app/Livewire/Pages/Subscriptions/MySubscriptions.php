<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Subscriptions;

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
final class MySubscriptions extends Component
{
    use WithPagination;

    public string $filter = 'all';
    public string $search = '';

    protected $queryString = [
        'filter' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render(SubscriptionService $subscriptionService): View
    {
        $user = Auth::user();
        
        $subscriptions = $user->subscriptions()
            ->with(['masterClass.chapters'])
            ->when($this->filter !== 'all', function ($query) {
                $query->where('status', $this->filter);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('masterClass', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $availableMasterClasses = $subscriptionService->getAvailableMasterClasses($user);

        return view('livewire.pages.subscriptions.my-subscriptions', [
            'subscriptions' => $subscriptions,
            'availableMasterClasses' => $availableMasterClasses,
        ]);
    }

    public function subscribe(int $masterClassId): void
    {
        $masterClass = MasterClass::findOrFail($masterClassId);
        $user = Auth::user();
        
        $subscriptionService = app(SubscriptionService::class);
        
        if (!$subscriptionService->canAccessMasterClass($user, $masterClass)) {
            $this->dispatch('notify', [
                'message' => 'Cette formation est payante. Vous devez effectuer un paiement pour y accéder.',
                'type' => 'error'
            ]);
            return;
        }

        try {
            $subscriptionService->subscribeTo($user, $masterClass);
            
            $this->dispatch('notify', [
                'message' => 'Inscription réussie ! Vous pouvez maintenant accéder à votre formation.',
                'type' => 'success'
            ]);
            
            $this->redirect(route('student.course.learning', $masterClass), navigate: true);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Une erreur est survenue lors de l\'inscription.',
                'type' => 'error'
            ]);
        }
    }
}
