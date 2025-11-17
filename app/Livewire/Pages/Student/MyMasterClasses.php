<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Student;

use Illuminate\Contracts\View\View;
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
        return view('livewire.pages.student.my-master-classes');
    }
}
