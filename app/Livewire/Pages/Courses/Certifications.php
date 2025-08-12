<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('welcome', ['whiteHeader' => true])]
#[Title('Certifications')]
final class Certifications extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public ?string $search = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Formation::query()
            ->active()
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->whereLike(['title', 'description'], sprintf('%%%s%%', $this->search));
        }

        $formationTotalCount = cache()->remember('formation_total_count', 60 * 10, function () {
            return Formation::count();
        });

        return view('livewire.pages.courses.certifications', [
            'formations' => $query->paginate(10),
            'formationCount' => $formationTotalCount,
        ]);
    }
}
