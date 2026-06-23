<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Formation;
use Filament\Notifications\Notification;
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

    public int $chapterCount = 0;

    protected $listeners = [
        'notify' => '$refresh',
    ];

    public function mount(Formation $formation): void
    {
        $this->formation = $formation->load([
            'sections.chapters',
        ]);

        $this->chapterCount = $this->formation->sections->flatMap->chapters->count();

        if (auth()->check() && auth()->user()->hasStudent()) {
            $enrollment = auth()->user()->enrollments()
                ->whereBelongsTo($this->formation)
                ->first();

            $this->isEnrolled = $enrollment !== null;
            $this->enrollmentStatus = $enrollment?->status;
        }
    }

    public function enroll(Formation $formation): void
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('formation.show', $this->formation));
            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (! auth()->user()->hasStudent()) {
            Notification::make()
                ->title('Seuls les étudiants peuvent s\'inscrire aux formations.')
                ->body('Veuillez vous connecter avec un compte étudiant pour continuer.')
                ->danger();

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

        if ($user->enrollments()->where('formation_id', $this->formation->id)->exists()) {
            $this->dispatch('notify', message: 'Vous êtes déjà inscrit à cette formation.', type: 'info');

            return;
        }

        // Créer l'inscription gratuite
        $user->enrollments()->create([
            'formation_id' => $this->formation->id,
            'enrollment_date' => now(),
            'status' => EnrollmentStatusEnum::ACTIVE->value,
            'payment_status' => EnrollmentPaymentEnum::FREE,
            'amount_paid' => 0,
            'progress_percentage' => 0,
        ]);

        $this->isEnrolled = true;
        $this->enrollmentStatus = 'active';

        Notification::make()
            ->title('Inscription réussie !')
            ->body('Vous êtes maintenant inscrit à la formation.')
            ->success();

        $this->redirect(route('course.player', ['formation' => $this->formation->id]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.frontend.show-formation.detail-formation');
    }
}
