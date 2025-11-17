<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Frontend\Payments;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Paiement & Confirmation')]
final class StudentPayment extends Component
{
    public Formation $formation;

    public string $code = '';

    public bool $isCodeSent = false;

    public function mount(Formation $formation): void
    {
        $this->formation = $formation;
    }

    public function confirmEnrollment(): void
    {
        if (mb_strlen($this->code) < 6) {
            $this->dispatch(
                'notify',
                message: 'Le code doit contenir au moins 6 caractères.',
                type: 'error'
            );
        }

        $user = auth()->user();
        $payment = $user
            ->payments()
            ->where('formation_id', $this->formation->id)
            ->exists();

        if (
            $payment &&
            ! $user->enrollments()
                ->where('formation_id', $this->formation->id)
                ->exists()
        ) {
            $user->enrollments()->create([
                'formation_id' => $this->formation->id,
                'enrollment_date' => Carbon::now(),
                'status' => EnrollmentStatusEnum::Active,
                'payment_status' => EnrollmentPaymentEnum::PAID,
                'progress_percentage' => 0,
            ]);
        }

        $this->dispatch(
            'notify',
            message: 'Inscription réussie ! Vous pouvez maintenant accéder à la formation.',
            type: 'success'
        );

        $this->redirect(route('formation.show', $this->formation), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.pages.frontend.payments.student-payment');
    }
}
