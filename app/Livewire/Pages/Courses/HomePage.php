<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title("Home Page")]
final class HomePage extends Component
{
    public function render(): View
    {
        return view('livewire.pages.courses.home-page', [
            'course' => MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED->value)
                ->latest('created_at')
                ->first(),
        ]);
    }
}
