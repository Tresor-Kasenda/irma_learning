<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CertificateStatusEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\ExamAttemptEnum;
use App\Enums\UserProgressEnum;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class CourseProgressionService
{
    /**
     * Ordered sections with their active chapters and exam eager-loaded.
     *
     * @return Collection<int, Section>
     */
    public function orderedSections(Formation $formation): Collection
    {
        return $formation->sections()
            ->with([
                'chapters' => fn ($query) => $query->where('is_active', true)->orderBy('order_position'),
                'exam',
            ])
            ->get();
    }

    /**
     * Per-section gating state used to lock/unlock the learning path sequentially.
     *
     * @return SupportCollection<int, array{id:int, unlocked:bool, chapters_complete:bool, exam_id:int|null, exam_passed:bool|null, complete:bool, needs_exam:bool}>
     */
    public function sectionStates(User $user, Formation $formation): SupportCollection
    {
        $sections = $this->orderedSections($formation);
        $completedChapterIds = $this->completedChapterIds($user, $formation);
        $previousComplete = true;

        return $sections->map(function (Section $section) use ($user, $completedChapterIds, &$previousComplete): array {
            $chaptersComplete = $this->sectionChaptersComplete($section, $completedChapterIds);
            $exam = $this->sectionExam($section);
            $examPassed = $exam ? $exam->hasUserPassed($user) : null;
            $complete = $chaptersComplete && ($exam === null || $examPassed === true);
            $unlocked = $previousComplete;

            $state = [
                'id' => $section->id,
                'unlocked' => $unlocked,
                'chapters_complete' => $chaptersComplete,
                'exam_id' => $exam?->id,
                'exam_passed' => $examPassed,
                'complete' => $complete,
                'needs_exam' => $exam !== null && $chaptersComplete && $examPassed !== true && $unlocked,
            ];

            $previousComplete = $previousComplete && $complete;

            return $state;
        });
    }

    public function isSectionComplete(User $user, Section $section, ?array $completedChapterIds = null): bool
    {
        $completedChapterIds ??= $this->completedChapterIds($user, $section->formation);

        if (! $this->sectionChaptersComplete($section, $completedChapterIds)) {
            return false;
        }

        $exam = $this->sectionExam($section);

        return $exam === null || $exam->hasUserPassed($user);
    }

    public function isFormationComplete(User $user, Formation $formation): bool
    {
        $completedChapterIds = $this->completedChapterIds($user, $formation);

        return $this->orderedSections($formation)
            ->every(fn (Section $section): bool => $this->isSectionComplete($user, $section, $completedChapterIds));
    }

    /**
     * Average of the learner's best percentage across every section that has an exam.
     * Returns null when the formation has no section exams.
     */
    public function finalScore(User $user, Formation $formation): ?float
    {
        $scores = $this->orderedSections($formation)
            ->map(fn (Section $section): ?float => $this->bestSectionExamPercentage($user, $section))
            ->filter(fn (?float $score): bool => $score !== null);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2);
    }

    /**
     * Marks the enrollment as completed once the whole formation is finished, and issues
     * the certificate when every section exam has been passed.
     */
    public function syncCompletion(User $user, Formation $formation): ?Certificate
    {
        if (! $this->isFormationComplete($user, $formation)) {
            return null;
        }

        $this->markEnrollmentCompleted($user, $formation);

        return $this->issueCertificate($user, $formation);
    }

    /**
     * @return array<int, int>
     */
    public function completedChapterIds(User $user, Formation $formation): array
    {
        $chapterIds = $this->orderedSections($formation)
            ->flatMap(fn (Section $section) => $section->chapters->pluck('id'))
            ->all();

        if ($chapterIds === []) {
            return [];
        }

        return UserProgress::query()
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $chapterIds)
            ->where('status', UserProgressEnum::COMPLETED->value)
            ->pluck('trackable_id')
            ->all();
    }

    public function sectionExam(Section $section): ?Exam
    {
        $exam = $section->exam;

        return $exam && $exam->is_active ? $exam : null;
    }

    /**
     * A section exam can only be taken once every active chapter of the section is completed.
     */
    public function hasCompletedSectionChapters(User $user, Section $section): bool
    {
        $section->loadMissing(['chapters' => fn ($query) => $query->where('is_active', true)]);

        $chapterIds = $section->chapters->pluck('id');

        if ($chapterIds->isEmpty()) {
            return true;
        }

        $completed = UserProgress::query()
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $chapterIds)
            ->where('status', UserProgressEnum::COMPLETED->value)
            ->count();

        return $completed === $chapterIds->count();
    }

    /**
     * @param  array<int, int>  $completedChapterIds
     */
    private function sectionChaptersComplete(Section $section, array $completedChapterIds): bool
    {
        $chapterIds = $section->chapters->pluck('id');

        if ($chapterIds->isEmpty()) {
            return true;
        }

        return $chapterIds->every(fn (int $id): bool => in_array($id, $completedChapterIds, true));
    }

    private function bestSectionExamPercentage(User $user, Section $section): ?float
    {
        $exam = $this->sectionExam($section);

        if (! $exam) {
            return null;
        }

        $best = $exam->attempts()
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::COMPLETED->value)
            ->max('percentage');

        return $best === null ? null : (float) $best;
    }

    private function issueCertificate(User $user, Formation $formation): ?Certificate
    {
        $finalScore = $this->finalScore($user, $formation);

        if ($finalScore === null) {
            return null;
        }

        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->where('status', CertificateStatusEnum::ACTIVE->value)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Certificate::create([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'final_score' => $finalScore,
            'issue_date' => now(),
            'status' => CertificateStatusEnum::ACTIVE,
        ]);
    }

    private function markEnrollmentCompleted(User $user, Formation $formation): void
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->first();

        if ($enrollment && $enrollment->status !== EnrollmentStatusEnum::COMPLETED) {
            $enrollment->update([
                'status' => EnrollmentStatusEnum::COMPLETED,
                'completion_date' => now(),
            ]);
        }
    }
}
