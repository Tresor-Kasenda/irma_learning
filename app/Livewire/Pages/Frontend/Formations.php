<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Frontend;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Formations')]
final class Formations extends Component
{
    public function render(): View
    {
        return view('livewire.pages.frontend.formations', [
            'course' => Formation::query()
                ->active()
                ->latest('created_at')
                ->first(),
        ]);
    }
}
