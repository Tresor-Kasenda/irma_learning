<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Formation;
use App\Models\Section;

final class FormationAssessmentReadinessService
{
    /**
     * @return list<string>
     */
    public function issues(Formation $formation): array
    {
        $formation->load([
            'exam.questions.options',
            'sections' => fn ($query) => $query
                ->where('is_active', true)
                ->with([
                    'chapters' => fn ($query) => $query->where('is_active', true),
                    'exam.questions.options',
                ]),
        ]);

        if ($formation->sections->isEmpty()) {
            return ['Ajoutez au moins une section active à la formation.'];
        }

        $issues = [];
        $sectionsWithoutContent = $formation->sections
            ->filter(fn (Section $section): bool => $section->chapters->isEmpty())
            ->pluck('title');

        if ($sectionsWithoutContent->isNotEmpty()) {
            $issues[] = 'Ajoutez au moins un chapitre actif dans chaque section : '.$sectionsWithoutContent->join(', ').'.';
        }

        $sectionsWithoutValidExam = $formation->sections
            ->filter(fn (Section $section): bool => ! $section->exam?->isReadyForPublication())
            ->pluck('title');

        if ($sectionsWithoutValidExam->isNotEmpty()) {
            $issues[] = 'Ajoutez une évaluation active avec des questions et réponses valides à chaque section : '
                .$sectionsWithoutValidExam->join(', ').'.';
        }

        if ($formation->is_certifying && ! $formation->exam?->isReadyForPublication()) {
            $issues[] = 'Ajoutez un examen final actif avec des questions et réponses valides à cette formation certifiante.';
        }

        return $issues;
    }

    /**
     * @return list<string>
     */
    public function deactivateIfIncomplete(Formation $formation): array
    {
        if (! $formation->is_active) {
            return [];
        }

        $issues = $this->issues($formation);

        if ($issues !== []) {
            $formation->update(['is_active' => false]);
        }

        return $issues;
    }
}
