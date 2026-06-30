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

        $formation->load(['sections.chapters' => function ($query) {
            $query->where('is_active', true)->orderBy('order_position');
        }]);

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
        } else {
            $lastProgress = UserProgress::where('user_id', $user->id)
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', $accessibleChapters->pluck('id'))
                ->where('status', UserProgressEnum::IN_PROGRESS)
                ->latest('updated_at')
                ->first();

            if ($lastProgress) {
                $currentChapter = $accessibleChapters->firstWhere('id', $lastProgress->trackable_id);
            }
        }

        if (! $currentChapter) {
            $currentChapter = $accessibleChapters->first() ?? $allChapters->first();
        }

        $currentChapterPosition = $currentChapter
            ? $allChapters->search(fn ($chapter) => $chapter->id === $currentChapter->id)
            : false;
        $currentChapterIndex = $currentChapterPosition === false ? 0 : $currentChapterPosition;

        if ($currentChapter && ! UserProgress::where([
            'user_id' => $user->id,
            'trackable_type' => Chapter::class,
            'trackable_id' => $currentChapter->id,
        ])->exists()) {
            UserProgress::create([
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $currentChapter->id,
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }

        $completedChapters = UserProgress::where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $allChapters->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->pluck('trackable_id')
            ->toArray();

        return Inertia::render('Student/Learnings/StudentLearningPlay', [
            'formation' => $formation,
            'enrollment' => $enrollment,
            'allChapters' => $allChapters->values()->toArray(),
            'currentChapter' => $currentChapter,
            'currentChapterIndex' => $currentChapterIndex,
            'completedChapters' => $completedChapters,
            'sections' => $sectionStates->values(),
            'htmlContent' => $currentChapter?->getHtmlContent() ?? '',
        ]);
    }

    public function completeChapter(Formation $formation, Chapter $chapter, CourseProgressionService $progression): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $chapter->section()->where('formation_id', $formation->id)->exists(),
            404,
        );

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->where('status', EnrollmentStatusEnum::ACTIVE->value)
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

        $this->updateChapterProgress($user, $formation, $progression);

        $certificate = $progression->syncCompletion($user, $formation);

        return $this->nextLearningStep($user, $formation, $chapter, $progression, $certificate);
    }

    public function detailCourse(Formation $formation)
    {
        $user = auth()->user();

        $formation->load([
            'sections' => fn ($q) => $q->orderBy('order_position')->with([
                'chapters' => fn ($q) => $q->where('is_active', true)->orderBy('order_position'),
            ]),
        ]);
        $formation->loadCount(Formation::catalogCountRelations());

        $chapterCount = $formation->sections->flatMap->chapters->count();

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->first();

        $completedChapterIds = [];

        if ($enrollment) {
            $allChapterIds = $formation->sections->flatMap->chapters->pluck('id');
            $completedChapterIds = UserProgress::query()
                ->where('user_id', $user->id)
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', $allChapterIds)
                ->where('status', UserProgressEnum::COMPLETED)
                ->pluck('trackable_id')
                ->toArray();
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
            'certificate' => $certificate,
        ]);
    }

    private function updateChapterProgress(User $user, Formation $formation, CourseProgressionService $progression): void
    {
        $allChapterIds = $progression->orderedSections($formation)
            ->flatMap(fn (Section $section) => $section->chapters->pluck('id'));

        $total = $allChapterIds->count();

        if ($total === 0) {
            return;
        }

        $completed = count($progression->completedChapterIds($user, $formation));
        $percentage = round($completed / $total * 100, 2);

        Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->first()
            ?->update(['progress_percentage' => $percentage]);
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
            ]);
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
