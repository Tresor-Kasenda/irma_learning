<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('welcome')]
final class FormationLists extends Component
{
    public function render(): View
    {
        return view('livewire.pages.courses.formation-lists', [
            'formations' => MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED->value)
                ->limit(6)
                ->get(),
        ]);
    }
}
