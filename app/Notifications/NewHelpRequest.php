<?php

namespace App\Notifications;

use App\Models\HelpRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewHelpRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public HelpRequest $request)
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
            ->line("email" . $this->request->email)
            ->line("message" . $this->request->message)
            ->line('request' . $this->request->problem->name)
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!')
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
