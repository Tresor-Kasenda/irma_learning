<?php

namespace App\Notifications;

use App\Models\FormationAccessCode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormationAccessCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public FormationAccessCode $accessCode
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code d\'accès à la formation')
            ->greeting('Bonjour ' . $notifiable->name)
            ->line('Merci pour votre inscription à la formation ' . $this->accessCode->formation->title)
            ->line('Voici votre code d\'accès unique : ' . $this->accessCode->code)
            ->line('Ce code est valable jusqu\'au ' . $this->accessCode->expires_at->format('d/m/Y H:i'))
            ->line('Attention : ce code ne peut être utilisé qu\'une seule fois.')
            ->action('Accéder à la formation', route('student.formations.validate-code', [
                'formation' => $this->accessCode->formation
            ]))
            ->line('Si vous n\'avez pas demandé ce code, aucune action n\'est requise.');
    }
}
