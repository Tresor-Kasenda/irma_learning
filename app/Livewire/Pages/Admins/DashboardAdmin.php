<?php

namespace App\Livewire\Pages\Admins;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Administration')]
class DashboardAdmin extends Component
{
    #[Url(as: 'tab')]
    public string $activeTab = 'overview';

    #[Computed]
    public function enrollments(): Collection
    {
        return auth()->user()
            ->enrollments()
            ->with([
                'formation:id,title,slug,difficulty_level,price',
                'formation.modules:id,formation_id,title',
            ])
            ->paid()
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $user = auth()->user();

        return [
            'total_enrollments' => $this->enrollments->count(),
            'completed_formations' => $this->enrollments->where('status', 'completed')->count(),
            'in_progress' => $this->enrollments->where('status', 'active')->count(),
            'certificates_earned' => $user->certificates()->valid()->count(),
            'total_time_spent' => $user->progress()->sum('time_spent'),
            'average_progress' => $this->enrollments->avg('progress_percentage') ?? 0,
        ];
    }

    #[Computed]
    public function recentActivity(): Collection
    {
        return auth()->user()
            ->progress()
            ->with(['trackable'])
            ->latest('updated_at')
            ->limit(10)
            ->get();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render(): View
    {
        return view('livewire.pages.admins.dashboard-admin');
    }
}
