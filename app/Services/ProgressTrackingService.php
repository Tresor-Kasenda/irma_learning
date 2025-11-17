<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Collection;

final class ProgressTrackingService
{
    /**
     * Marquer un chapitre comme étant en cours
     */
    public function markChapterAsInProgress(User $user, Chapter $chapter): UserProgress
    {
        return UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $chapter->id,
            ],
            [
                'status' => UserProgressEnum::IN_PROGRESS->value,
                'started_at' => now(),
            ]
        );
    }

    /**
     * Marquer un chapitre comme complété
     */
    public function markChapterAsCompleted(User $user, Chapter $chapter): UserProgress
    {
        return UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $chapter->id,
            ],
            [
                'progress_percentage' => 100,
                'status' => UserProgressEnum::COMPLETED->value,
                'completed_at' => now(),
                'time_spent' => ($chapter->duration_minutes ?? 0) * 60,
            ]
        );
    }

    /**
     * Obtenir le dernier chapitre en cours pour une formation
     */
    public function getLastInProgressChapter(User $user, array $chapterIds): ?UserProgress
    {
        return UserProgress::where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $chapterIds)
            ->where('status', UserProgressEnum::IN_PROGRESS)
            ->latest('updated_at')
            ->first();
    }

    /**
     * Mettre à jour la progression d'une inscription
     */
    public function updateEnrollmentProgress(Enrollment $enrollment, array $chapterIds): void
    {
        $progressPercentage = $this->calculateFormationProgress(
            $enrollment->user,
            $chapterIds
        );

        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? EnrollmentStatusEnum::Completed : EnrollmentStatusEnum::Active,
            'completion_date' => $progressPercentage >= 100 ? now() : null,
        ]);
    }

    /**
     * Calculer le pourcentage de progression pour une formation
     */
    public function calculateFormationProgress(User $user, array $chapterIds): float
    {
        $totalChapters = count($chapterIds);

        if ($totalChapters === 0) {
            return 0;
        }

        $completedChapters = $this->getCompletedChapters($user, $chapterIds)->count();

        return round(($completedChapters / $totalChapters) * 100, 2);
    }

    /**
     * Obtenir tous les chapitres complétés d'un utilisateur pour une formation
     */
    public function getCompletedChapters(User $user, array $chapterIds): Collection
    {
        return UserProgress::query()
            ->whereBelongsTo($user)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $chapterIds)
            ->where('status', '=', UserProgressEnum::COMPLETED->value)
            ->get();
    }

    /**
     * Obtenir les statistiques de progression pour un utilisateur
     */
    public function getUserProgressStats(User $user): array
    {
        $totalEnrollments = $user->enrollments()->count();
        $activeEnrollments = $user->enrollments()
            ->where('status', EnrollmentStatusEnum::Active)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->count();

        $completedEnrollments = $user->enrollments()
            ->where('status', EnrollmentStatusEnum::Completed)
            ->count();

        $averageProgress = (int) $user->enrollments()
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->avg('progress_percentage');

        $totalTimeSpent = UserProgress::where('user_id', $user->id)->sum('time_spent');

        $certificatesEarned = $user->certificates()
            ->where('status', 'active')
            ->count();

        return [
            'totalEnrollments' => $totalEnrollments,
            'activeEnrollments' => $activeEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'averageProgress' => $averageProgress,
            'totalTimeSpent' => $totalTimeSpent,
            'certificatesEarned' => $certificatesEarned,
        ];
    }
}
