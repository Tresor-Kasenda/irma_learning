<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\EnrollmentPaymentEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Services\CourseProgressionService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Identifie le prochain chapitre ou examen obligatoire de l’utilisateur, sans modifier sa progression.')]
#[IsIdempotent]
#[IsReadOnly]
final class MyNextLearningStepTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, CourseProgressionService $progression): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Utilisateur non authentifié.');
        }

        $arguments = $request->validate([
            'formation_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->whereHas('formation', fn ($query) => $query->active())
            ->with('formation')
            ->when(isset($arguments['formation_id']), fn ($query) => $query->where('formation_id', $arguments['formation_id']))
            ->orderByDesc('last_accessed_at')
            ->latest('updated_at')
            ->first();

        if (! $enrollment instanceof Enrollment) {
            return Response::error('Aucune formation accessible ne correspond à cette demande.');
        }

        $formation = $enrollment->formation;
        $sections = $progression->orderedSections($formation);
        $sectionStates = $progression->sectionStates($user, $formation)->keyBy('id');
        $completedChapterIds = $progression->completedChapterIds($user, $formation);

        foreach ($sections as $section) {
            $state = $sectionStates->get($section->id);

            if (! ($state['unlocked'] ?? false)) {
                continue;
            }

            $nextChapter = $section->chapters
                ->first(fn (Chapter $chapter): bool => ! in_array($chapter->id, $completedChapterIds, true));

            if ($nextChapter instanceof Chapter) {
                return Response::structured($this->chapterStep($formation, $section, $nextChapter));
            }

            if (($state['needs_exam'] ?? false) === true) {
                return Response::structured([
                    'type' => 'section_exam',
                    'status' => 'available',
                    'formation_id' => $formation->id,
                    'formation_title' => $formation->title,
                    'section_id' => $section->id,
                    'section_title' => $section->title,
                    'exam_id' => $state['exam_id'],
                    'exam_title' => $state['exam_title'],
                    'message' => 'Réussissez cette évaluation pour déverrouiller la section suivante.',
                ]);
            }

            if (($state['exam_missing'] ?? false) === true) {
                return Response::structured([
                    'type' => 'section_exam',
                    'status' => 'configuration_required',
                    'formation_id' => $formation->id,
                    'formation_title' => $formation->title,
                    'section_id' => $section->id,
                    'section_title' => $section->title,
                    'message' => 'L’évaluation obligatoire de cette section n’est pas encore configurée.',
                ]);
            }
        }

        if ($formation->is_certifying) {
            $finalExam = $progression->formationExam($formation);

            if ($finalExam === null) {
                return Response::structured([
                    'type' => 'formation_exam',
                    'status' => 'configuration_required',
                    'formation_id' => $formation->id,
                    'formation_title' => $formation->title,
                    'message' => 'L’évaluation finale obligatoire n’est pas encore configurée.',
                ]);
            }

            if (! $finalExam->hasUserPassed($user)) {
                return Response::structured([
                    'type' => 'formation_exam',
                    'status' => 'available',
                    'formation_id' => $formation->id,
                    'formation_title' => $formation->title,
                    'exam_id' => $finalExam->id,
                    'exam_title' => $finalExam->title,
                    'message' => 'Réussissez l’évaluation finale pour terminer cette formation certifiante.',
                ]);
            }
        }

        return Response::structured([
            'type' => 'formation_complete',
            'status' => 'complete',
            'formation_id' => $formation->id,
            'formation_title' => $formation->title,
            'message' => 'Cette formation est terminée.',
        ]);

        return Response::text('The content generated by the tool.');
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'formation_id' => $schema->integer()
                ->description('Identifiant facultatif de la formation. Sans valeur, la dernière formation consultée est utilisée.'),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function chapterStep(Formation $formation, Section $section, Chapter $chapter): array
    {
        return [
            'type' => 'chapter',
            'status' => 'available',
            'formation_id' => $formation->id,
            'formation_title' => $formation->title,
            'section_id' => $section->id,
            'section_title' => $section->title,
            'chapter_id' => $chapter->id,
            'chapter_title' => $chapter->title,
            'message' => 'Voici le prochain chapitre accessible.',
        ];
    }
}
