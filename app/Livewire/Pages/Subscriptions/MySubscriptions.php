<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Subscriptions;

use Illuminate\Contracts\View\View;
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

    public function render(): View
    {
        return view('livewire.pages.subscriptions.my-subscriptions');
    }
}
