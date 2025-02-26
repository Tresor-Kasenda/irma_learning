<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAccountCreated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    )
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre compte a été créé')
            ->greeting("Bonjour {$this->name},")
            ->line('Votre compte a été créé avec succès.')
            ->line("Email: {$this->email}")
            ->line("Mot de passe: {$this->password}")
            ->action('Se connecter', url('/admin/login'))
            ->line('Merci de votre confiance!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
