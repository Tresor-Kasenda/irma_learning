<?php

namespace App\Livewire\Pages\Formations;

use App\Models\Training;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Formation Details')]
class DetailFormation extends Component
{
    #[Locked]
    public Training $training;

    public function mount(Training $training): void
    {
        $this->training = $training;
    }

    public function render(): View
    {
        return view('livewire.pages.formations.detail-formation');
    }
}
