<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('welcome', ['whiteHeader' => true])]
#[Title('Formations')]
final class FormationsLists extends Component
{
    #[Url(as: 'q')]
    #[Validate('nullable|string')]
    public ?string $search = null;

    public function render(): View
    {
        $formations = Formation::query()
            ->where('is_active', true)
            ->when(
                $this->search,
                fn ($query) => $query
                    ->where(function ($q) {
                        $q->where('title', 'like', sprintf('%%%s%%', $this->search))
                            ->orWhere('description', 'like', sprintf('%%%s%%', $this->search));
                    })
            )
            ->latest('created_at')
            ->take(12)
            ->get();

        return view('livewire.pages.courses.formations-lists', [
            'formations' => $formations,
            'trainings' => Formation::query()
                ->where('is_active', true)
                ->latest('created_at')
                ->take(9)
                ->get(),

        ]);
    }
}
