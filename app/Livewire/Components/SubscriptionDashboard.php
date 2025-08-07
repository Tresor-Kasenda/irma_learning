<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class SubscriptionDashboard extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function render(): View
    {
        return view('livewire.components.subscription-dashboard');
    }

    private function loadStats(): void
    {
        $user = Auth::user();
        $subscriptionService = app(SubscriptionService::class);
        
        $subscriptions = $subscriptionService->getUserSubscriptions($user);
        
        $this->stats = [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $subscriptions->where('status.value', 'active')->count(),
            'completed_subscriptions' => $subscriptions->where('status.value', 'completed')->count(),
            'average_progress' => $subscriptions->avg('progress') ?? 0,
            'recent_activity' => $subscriptions->take(3),
        ];
    }
}
