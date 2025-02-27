<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordChangeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user)
    {
        //
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
            ->subject('Votre mot de passe a été mis à jour')
            ->greeting('Bonjour ' . $this->user->name . ' ' . $this->user->firstname)
            ->line("Nous vous confirmons que votre mot de passe a été changé avec succès.")
            ->action("Si vous n’êtes pas à l’origine de cette modification, veuillez immédiatement réinitialiser votre mot de passe en cliquant sur et contacter notre support à support@irmardc.org.", route('password.change'))
            ->line('Merci de votre confiance')
            ->line('L’équipe iRMA');
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
