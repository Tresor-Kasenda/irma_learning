<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Formation;
use App\Models\VerificationCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EnrollmentVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly VerificationCode $verificationCode,
        private readonly Formation        $formation
    )
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Code de vérification pour votre inscription')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous avez demandé à vous inscrire à la formation : **' . $this->formation->title . '**')
            ->line('Voici votre code de vérification :')
            ->line('**' . $this->verificationCode->code . '**')
            ->line('Ce code est valide pendant 24 heures et ne peut être utilisé qu\'une seule fois.')
            ->action('Continuer l\'inscription', url('/formations/' . $this->formation->slug . '/enroll'))
            ->line('Si vous n\'avez pas demandé cette inscription, ignorez cet email.')
            ->salutation('Cordialement, L\'équipe de formation');
    }
}
