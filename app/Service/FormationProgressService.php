<?php

namespace App\Service;

use App\Models\Formation;
use App\Models\User;

class FormationProgressService
{
    public function getFormationStats(Formation $formation): array
    {
        $totalStudents = $formation->getPaidEnrollmentsCount();
        $certifiedStudents = $formation->getCertifiedStudentsCount();

        return [
            'total_students' => $totalStudents,
            'certified_students' => $certifiedStudents,
            'certification_rate' => $totalStudents > 0 ? ($certifiedStudents / $totalStudents) * 100 : 0,
            'total_modules' => $formation->modules()->count(),
            'total_sections' => $formation->getTotalSectionsCount(),
            'total_chapters' => $formation->getTotalChaptersCount(),
        ];
    }

    public function getUserProgress(User $user, Formation $formation): array
    {
        $enrollment = $user->enrollments()
            ->where('formation_id', $formation->id)
            ->first();

        if (!$enrollment) {
            return ['enrolled' => false];
        }

        return [
            'enrolled' => true,
            'progress_percentage' => $enrollment->progress_percentage,
            'completed_chapters' => $formation->getCompletedChaptersCount($user),
            'total_chapters' => $formation->getTotalChaptersCount(),
            'completed_sections' => $formation->getCompletedSectionsCount($user),
            'total_sections' => $formation->getTotalSectionsCount(),
            'completed_modules' => $formation->getCompletedModulesCount($user),
            'total_modules' => $formation->modules()->count(),
        ];
    }
}
