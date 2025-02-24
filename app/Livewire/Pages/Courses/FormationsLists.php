<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Formations')]
final class FormationsLists extends Component
{
    #[Url(as: 'q')]
    public ?string $search = null;

    
    public function render(): View
    {
        return view('livewire.pages.courses.formations-lists');
    }
}
