<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Frontend\ShowFormation;

use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Detail sur le formation')]
final class DetailFormation extends Component
{
    #[Locked]
    public Formation $formation;

    public $isEnrolled = false;

    public $enrollmentStatus = null;

    public int $moduleCount = 0;

    public int $chapterCount = 0;

    protected $listeners = [
        'notify' => '$refresh',
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
        if (! auth()->check()) {
            session()->put('url.intended', route('formation.show', $this->formation));
            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (! auth()->user()->hasStudent()) {
            $this->dispatch(
                'notify',
                message: 'Seuls les étudiants peuvent s\'inscrire aux formations.',
                type: 'error'
            );

            return;
        }

        // Si déjà inscrit, rediriger vers la formation
        if ($this->isEnrolled) {
            $this->redirect(route('course.player', ['formation' => $this->formation->id]), navigate: true);

            return;
        }

        // Si la formation est gratuite, inscrire directement
        if ($this->formation->price === 0) {
            $this->enrollFree();

            return;
        }

        // Sinon, rediriger vers la page de paiement
        $this->redirect(route('student.payment.create', $this->formation), navigate: true);
    }

    public function enrollFree(): void
    {
        $user = auth()->user();

        // Vérifier si déjà inscrit
        if ($user->enrollments()->where('formation_id', $this->formation->id)->exists()) {
            $this->dispatch('notify', message: 'Vous êtes déjà inscrit à cette formation.', type: 'info');

            return;
        }

        // Créer l'inscription gratuite
        $user->enrollments()->create([
            'formation_id' => $this->formation->id,
            'enrollment_date' => now(),
            'status' => \App\Enums\EnrollmentStatusEnum::Active,
            'payment_status' => \App\Enums\EnrollmentPaymentEnum::FREE,
            'amount_paid' => 0,
            'progress_percentage' => 0,
        ]);

        $this->isEnrolled = true;
        $this->enrollmentStatus = 'active';

        $this->dispatch('notify', message: 'Inscription réussie ! Vous pouvez maintenant commencer la formation.', type: 'success');

        // Rediriger vers la page de cours
        $this->redirect(route('course.player', ['formation' => $this->formation->id]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.frontend.show-formation.detail-formation');
    }
}
