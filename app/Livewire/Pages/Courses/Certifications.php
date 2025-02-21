<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Certifications')]
final class Certifications extends Component
{
    public function render(): View
    {
        return view('livewire.pages.courses.certifications');
    }
}
