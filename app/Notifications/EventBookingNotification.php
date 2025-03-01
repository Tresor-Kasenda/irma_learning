<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EventBookingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking)
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
            ->subject('Bienvenue sur la plateforme iRMA – Votre compte est prêt !')
            ->greeting('Bonjour'.$this->booking->name.' '.$this->booking->firstname)
            ->line('Félicitations !')
            ->line('Votre compte sur la plateforme d’apprentissage en ligne de l’Institut du Risk Management (iRMA) a été créé avec succès.')
            ->line("Votre adresse Email : {$this->booking->email}")
            ->line("Votre mot de passe : {$this->booking->reference}")
            ->action('Accès à votre compte  :', url('/login'))
            ->lines([
                'Nous sommes ravis de vous accompagner dans votre parcours de formation.',
                'Pour toute assistance, notre équipe reste à votre disposition.',
            ])
            ->line('Bonne formation !')
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
