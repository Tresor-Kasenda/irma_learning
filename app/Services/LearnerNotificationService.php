<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use App\Notifications\LearningActivityNotification;

final class LearnerNotificationService
{
    public function welcomeIfNeeded(User $user): void
    {
        if ($user->notifications()->where('type', 'learning.welcome')->doesntExist()) {
            $this->welcome($user);
        }
    }

    public function welcome(User $user): void
    {
        $this->send(
            $user,
            notificationType: 'learning.welcome',
            title: 'Bienvenue sur IRMA Learning',
            message: 'Votre espace apprenant est prêt. Découvrez les formations disponibles et commencez à apprendre à votre rythme.',
            actionUrl: route('dashboard'),
            actionLabel: 'Découvrir mon espace',
        );
    }

    public function paymentConfirmed(Enrollment $enrollment): void
    {
        $formation = $enrollment->formation;

        $this->send(
            $enrollment->user,
            notificationType: 'learning.payment-confirmed',
            title: 'Paiement confirmé',
            message: sprintf('Votre paiement pour « %s » a été confirmé. La formation est maintenant accessible.', $formation->title),
            actionUrl: route('course.player', $formation->id),
            actionLabel: 'Commencer la formation',
            tone: 'success',
        );
    }

    public function formationCompleted(User $user, Formation $formation): void
    {
        $this->send(
            $user,
            notificationType: 'learning.formation-completed',
            title: 'Formation terminée',
            message: sprintf('Félicitations ! Vous avez terminé « %s ».', $formation->title),
            actionUrl: route('student.learnings.detail', $formation->slug),
            actionLabel: 'Voir la formation',
            tone: 'success',
        );
    }

    public function certificateIssued(User $user, Certificate $certificate): void
    {
        $formation = $certificate->formation;

        $this->send(
            $user,
            notificationType: 'learning.certificate-issued',
            title: 'Votre certificat est disponible',
            message: sprintf('Votre certificat pour « %s » est prêt.', $formation->title),
            actionUrl: route('certificats.show', $certificate),
            actionLabel: 'Voir mon certificat',
            tone: 'celebration',
        );
    }

    private function send(
        User $user,
        string $notificationType,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        string $tone = 'info',
    ): void {
        $user->notify(new LearningActivityNotification(
            notificationType: $notificationType,
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            tone: $tone,
        ));
    }
}
