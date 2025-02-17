<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('welcome')]
final class Certifications extends Component
{
    public function render(): View
    {
        return view('livewire.pages.courses.certifications');
    }
}
