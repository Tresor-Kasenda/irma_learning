<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ExamSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExamSubmissionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ExamSubmission $submission)
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
            ->subject('Evaluation soumise – Bien joué !')
            ->greeting('Bonjour' . $this->submission->user->name . ' ' . $this->submission->user->firstname)
            ->line('Vous avez soumis avec succès votre évaluation pour le chapitre' . $this->submission->chapter->title . '.')
            ->line('Statut : En cours de correction')
            ->line('Accès à vos résultats : Vous serez notifié dès leur publication.')
            ->line('Continuez sur cette lancée et passez au chapitre suivant !')
            ->line('Bonne continuation')
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
