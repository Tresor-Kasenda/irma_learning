<?php

namespace App\Livewire\Pages;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title("Page d'accueil")]
class HomePage extends Component
{
    public function render(): View
    {
        return view('livewire.pages.home-page', [
            'formation' => Formation::query()
                ->active()
                ->latest('created_at')
                ->first(),
        ]);
    }
}
