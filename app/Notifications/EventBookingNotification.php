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
            ->greeting("Bonjour {$notifiable->name}")
            ->line("Vous avez été inscrit à l'événement {$this->booking->event->title}")
            ->line('Voici les détails de votre inscription :')
            ->line("Nom : {$this->booking->name}")
            ->line("Email : {$this->booking->email}")
            ->line("Mot de passe : {$this->booking->reference}")
            ->line('Apres la master classe vous aurez un acces la plaforme de certification sur le lien ci dessous')
            ->action('Certification', url('/'))
            ->line('Merci de votre confiance.');
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
