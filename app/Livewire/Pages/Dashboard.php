<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Administration')]
final class Dashboard extends Component
{
    #[Url('q')]
    #[Validate('nullable|string')]
    public ?string $search = null;

    private DashboardService $dashboardService;

    public function mount(DashboardService $dashboardService): void
    {
        $this->dashboardService = $dashboardService;
    }

    public function render(): View
    {
        return view('dashboard');
    }

    #[Computed]
    public function statistics(): array
    {
        return $this->dashboardService->getUserStatistics(Auth::user());
    }

    #[Computed]
    public function masterClasses(): Collection
    {
        return $this->dashboardService->getUserSubscribedMasterClasses(Auth::user(), $this->search);
    }
}
