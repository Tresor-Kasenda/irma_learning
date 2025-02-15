<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Administration')]
final class Dashboard extends Component
{
    public function render(): View
    {
        return view('dashboard');
    }

    #[Computed]
    public function masterClasses(): \Illuminate\Database\Eloquent\Collection
    {
        return MasterClass::query()
            ->withCount('chapters')
            ->where('status', '=', MasterClassEnum::PUBLISHED->value)
            ->orderByDesc('created_at')
            ->get();
    }
}
