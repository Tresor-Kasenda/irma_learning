<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Certifications')]
final class Certifications extends Component
{
    #[Url(as: 'q')]
    public ?string $search = null;

    public $formations;

    public function updatedSearch(): void
    {
        $this->getFormations();
    }

    public function getFormations(): void
    {
        $this->formations = MasterClass::query()
            ->where('status', '=', MasterClassEnum::PUBLISHED->value)
            ->when($this->search, function ($query) {
                $query->whereAny([
                    'title',
                    'description',
                ], 'like', sprintf('%%%s%%', $this->search));
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function mount(): void
    {
        $this->getFormations();
    }

    public function render(): View
    {
        return view('livewire.pages.courses.certifications');
    }
}
