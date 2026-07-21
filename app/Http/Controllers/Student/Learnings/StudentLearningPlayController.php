<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Learnings;

use App\Enums\CertificateStatusEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use App\Services\CourseProgressionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StudentLearningPlayController extends Controller
{
    public function __invoke(Formation $formation, CourseProgressionService $progression)
    {
        $user = auth()->user();

        $formation->load([
            'exam',
            'sections' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('order_position')
                ->with(['chapters' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('order_position')]),
        ]);

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->whereIn('status', [EnrollmentStatusEnum::ACTIVE, EnrollmentStatusEnum::COMPLETED])
            ->first();

        if (! $enrollment) {
            return redirect()->route('student.learnings.detail', $formation->slug)
                ->with('error', 'Vous devez être inscrit à cette formation pour y accéder.');
        }

        $sectionStates = $progression->sectionStates($user, $formation);
        $unlockedSectionIds = $sectionStates->where('unlocked', true)->pluck('id')->all();

        $allChapters = $formation->sections
            ->flatMap(fn ($section) => $section->chapters)
            ->values();

        $accessibleChapters = $allChapters
            ->filter(fn ($chapter) => in_array($chapter->section_id, $unlockedSectionIds, true))
            ->values();

        $chapterId = request()->query('chapterId');
        $currentChapter = null;

        if ($chapterId) {
            $currentChapter = $accessibleChapters->firstWhere('id', (int) $chapterId);
        }

        if (! $currentChapter) {
            $latestChapter = $progression->latestChapter($user, $formation);
            $currentChapter = $latestChapter
                ? $accessibleChapters->firstWhere('id', $latestChapter->id)
                : null;
        }

        if (! $currentChapter) {
            $currentChapter = $accessibleChapters->first() ?? $allChapters->first();
        }

        $currentChapterPosition = $currentChapter
            ? $allChapters->search(fn ($chapter) => $chapter->id === $currentChapter->id)
            : false;
        $currentChapterIndex = $currentChapterPosition === false ? 0 : $currentChapterPosition;

        if ($currentChapter) {
            $this->recordChapterVisit($user, $currentChapter, $enrollment);
        }

        $completedChapters = UserProgress::where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $allChapters->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->pluck('trackable_id')
            ->toArray();

        $finalExam = $progression->formationExam($formation);
        $sectionsComplete = $progression->areSectionsComplete($user, $formation);
        $finalExamPassed = $finalExam?->hasUserPassed($user) ?? false;
        $enrollment->setAttribute('progress_percentage', $progression->progressPercentage($user, $formation));

        return Inertia::render('Student/Learnings/StudentLearningPlay', [
            'formation' => $formation,
            'enrollment' => $enrollment,
            'allChapters' => $allChapters->values()->toArray(),
            'currentChapter' => $currentChapter,
            'currentChapterIndex' => $currentChapterIndex,
            'completedChapters' => $completedChapters,
            'sections' => $sectionStates->values(),
            'finalAssessment' => [
                'required' => $formation->is_certifying,
                'ready' => $sectionsComplete,
                'exam_id' => $finalExam?->id,
                'exam_title' => $finalExam?->title,
                'exam_missing' => $formation->is_certifying && $finalExam === null,
                'passed' => $finalExamPassed,
                'needs_exam' => $formation->is_certifying && $sectionsComplete && $finalExam !== null && ! $finalExamPassed,
            ],
            'htmlContent' => $currentChapter?->getHtmlContentRaw() ?? '',
        ]);
    }

    public function completeChapter(Formation $formation, Chapter $chapter, CourseProgressionService $progression): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $chapter->section()->where('formation_id', $formation->id)->exists(),
            404,
        );

        $chapter->loadMissing('section');

        abort_unless(
            $chapter->is_active
            && $chapter->section?->is_active
            && $progression->isSectionUnlocked($user, $chapter->section),
            403,
            'Réussissez l’évaluation de la section précédente avant de continuer.',
        );

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('status', [
                EnrollmentStatusEnum::ACTIVE->value,
                EnrollmentStatusEnum::COMPLETED->value,
            ])
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->exists();

        abort_unless($isEnrolled, 403);

        UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $chapter->id,
            ],
            [
                'progress_percentage' => 100,
                'status' => UserProgressEnum::COMPLETED,
                'completed_at' => now(),
                'time_spent' => ($chapter->duration_minutes ?? 0) * 60,
            ],
        );

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->first();

        $enrollment?->update(['last_accessed_at' => now()]);

        $progression->syncProgress($user, $formation);

        $certificate = $progression->syncCompletion($user, $formation);

        return $this->nextLearningStep($user, $formation, $chapter, $progression, $certificate);
    }

    public function detailCourse(Formation $formation, CourseProgressionService $progression)
    {
        $user = auth()->user();

        $formation->load([
            'sections' => fn ($q) => $q->orderBy('order_position')->with([
                'chapters' => fn ($q) => $q->where('is_active', true)->orderBy('order_position'),
                'exam',
            ]),
            'exam',
        ]);
        $formation->loadCount(Formation::catalogCountRelations());

        $chapterCount = $formation->sections->flatMap->chapters->count();

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->first();

        $completedChapterIds = [];
        $continueChapterId = null;
        $learningProgress = 0.0;

        if ($enrollment) {
            $allChapterIds = $formation->sections->flatMap->chapters->pluck('id');
            $completedChapterIds = UserProgress::query()
                ->where('user_id', $user->id)
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', $allChapterIds)
                ->where('status', UserProgressEnum::COMPLETED)
                ->pluck('trackable_id')
                ->toArray();
            $continueChapterId = $progression->latestChapter($user, $formation)?->id;
            $learningProgress = $progression->progressPercentage($user, $formation);
        }

        $certificate = Certificate::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->where('status', CertificateStatusEnum::ACTIVE->value)
            ->first(['id', 'certificate_number', 'final_score', 'issue_date']);

        return Inertia::render('Student/Learnings/CourseDetail', [
            'formation' => $formation,
            'chapterCount' => $chapterCount,
            'enrollment' => $enrollment,
            'completedChapterIds' => $completedChapterIds,
            'continueChapterId' => $continueChapterId,
            'learningProgress' => $learningProgress,
            'certificate' => $certificate,
        ]);
    }

    private function recordChapterVisit(User $user, Chapter $chapter, Enrollment $enrollment): void
    {
        $progress = UserProgress::query()
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->where('trackable_id', $chapter->id)
            ->first();

        if ($progress) {
            $updates = ['updated_at' => now()];

            if ($progress->status !== UserProgressEnum::COMPLETED) {
                $updates['status'] = UserProgressEnum::IN_PROGRESS;
                $updates['started_at'] = $progress->started_at ?? now();
            }

            $progress->forceFill($updates)->save();
        } else {
            UserProgress::create([
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $chapter->id,
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }

        $enrollment->update(['last_accessed_at' => now()]);
    }

    private function nextLearningStep(
        User $user,
        Formation $formation,
        Chapter $chapter,
        CourseProgressionService $progression,
        ?Certificate $certificate,
    ): RedirectResponse {
        $sections = $progression->orderedSections($formation);
        $section = $sections->firstWhere('id', $chapter->section_id);

        $orderedChapters = $sections->flatMap(fn (Section $s) => $s->chapters)->values();
        $position = $orderedChapters->search(fn (Chapter $c) => $c->id === $chapter->id);
        $next = $position === false ? null : $orderedChapters->get($position + 1);

        $sectionExam = $section ? $progression->sectionExam($section) : null;
        $sectionChaptersDone = $section
            && $this->sectionChaptersDone($section, $progression->completedChapterIds($user, $formation));
        $mustTakeSectionExam = $sectionExam
            && $sectionChaptersDone
            && ! $sectionExam->hasUserPassed($user);

        if ($mustTakeSectionExam) {
            return redirect()->route('exam.take', $sectionExam)
                ->with('info', 'Réussissez l\'examen de cette section pour débloquer la suivante.');
        }

        if ($next) {
            return redirect()->route('course.player', [
                'formation' => $formation->id,
                'chapterId' => $next->id,
            ])->with('success', 'Chapitre terminé. Vous pouvez poursuivre votre apprentissage.');
        }

        $message = $certificate
            ? 'Félicitations ! Vous avez terminé la formation et obtenu votre certificat.'
            : 'Félicitations ! Vous avez terminé tous les chapitres !';

        return redirect()->route('course.player', $formation->id)->with('success', $message);
    }

    /**
     * @param  array<int, int>  $completedChapterIds
     */
    private function sectionChaptersDone(Section $section, array $completedChapterIds): bool
    {
        $chapterIds = $section->chapters->pluck('id');

        if ($chapterIds->isEmpty()) {
            return true;
        }

        return $chapterIds->every(fn (int $id): bool => in_array($id, $completedChapterIds, true));
    }
}
