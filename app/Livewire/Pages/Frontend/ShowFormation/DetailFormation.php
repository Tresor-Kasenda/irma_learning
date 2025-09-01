<?php

namespace App\Livewire\Pages\Frontend\ShowFormation;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title("Detail sur le formation")]
class DetailFormation extends Component
{
    #[Locked]
    public Formation $formation;

    public $isEnrolled = false;
    public $enrollmentStatus = null;

    public int $moduleCount = 0;
    public int $chapterCount = 0;

    protected $listeners = [
        'notify' => '$refresh'
    ];

    public function mount(Formation $formation): void
    {
        $this->formation = $formation->load([
            'modules.sections.chapters',
            'creator',
        ]);

        $this->moduleCount = $this->formation->modules->count();
        $this->chapterCount = $this->formation->modules->flatMap->sections->flatMap->chapters->count();

        if (auth()->check() && auth()->user()->hasStudent()) {
            $enrollment = auth()->user()->enrollments()
                ->where('formation_id', $formation->id)
                ->first();

            $this->isEnrolled = $enrollment !== null;
            $this->enrollmentStatus = $enrollment?->status;
        }
    }


    public function enroll(): void
    {
        if (!auth()->check()) {
            session()->put('url.intended', route('formation.show', $this->formation));
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (!auth()->user()->hasStudent()) {
            $this->dispatch(
                'notify',
                message: 'Seuls les étudiants peuvent s\'inscrire aux formations.',
                type: 'error'
            );
            return;
        }

        // $payment = auth()->user()
        //     ->payments()
        //     ->where('formation_id', $this->formation->id)
        //     ->exists();

        // if (!$payment) {
        //     $this->redirect(route('student.payment.create', $this->formation), navigate: true);
        // }
        $this->redirect(route('student.payment.create', $this->formation), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.frontend.show-formation.detail-formation');
    }
}
