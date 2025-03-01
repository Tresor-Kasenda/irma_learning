<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Models\Training;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Formations')]
final class FormationsLists extends Component
{
    #[Url(as: 'q')]
    #[Validate('nullable|string')]
    public ?string $search = null;

    public function render(): View
    {
        $formations = Training::query()
            ->when(
                $this->search,
                fn($query) => $query
                    ->whereAny([
                        'title',
                        'description',
                    ], 'like', sprintf('%%%s%%', $this->search)))
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('livewire.pages.courses.formations-lists', [
            'formations' => $formations,
            'trainings' => Training::query()
                ->latest('created_at')
                ->take(9)
                ->get(),

        ]);
    }
}
