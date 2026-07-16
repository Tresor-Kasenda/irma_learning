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
     * Per-section gating state used to lock/unlock the learning path sequentially.
     *
     * @return SupportCollection<int, array{id:int, unlocked:bool, chapters_complete:bool, exam_id:int|null, exam_title:string|null, exam_passed:bool|null, exam_missing:bool, complete:bool, needs_exam:bool}>
     */
    public function sectionStates(User $user, Formation $formation): SupportCollection
    {
        $sections = $this->orderedSections($formation);
        $completedChapterIds = $this->completedChapterIds($user, $formation);
        $previousComplete = true;

        return $sections->map(function (Section $section) use ($user, $completedChapterIds, &$previousComplete): array {
            $chaptersComplete = $this->sectionChaptersComplete($section, $completedChapterIds);
            $exam = $this->sectionExam($section);
            $examPassed = $exam?->hasUserPassed($user);
            $complete = $chaptersComplete && $examPassed === true;
            $unlocked = $previousComplete;

            $state = [
                'id' => $section->id,
                'unlocked' => $unlocked,
                'chapters_complete' => $chaptersComplete,
                'exam_id' => $exam?->id,
                'exam_title' => $exam?->title,
                'exam_passed' => $examPassed,
                'exam_missing' => $exam === null,
                'complete' => $complete,
                'needs_exam' => $exam !== null && $chaptersComplete && $examPassed !== true && $unlocked,
            ];

            $previousComplete = $previousComplete && $complete;

            return $state;
        });
    }

    /**
     * Ordered sections with their active chapters and exam eager-loaded.
     *
     * @return Collection<int, Section>
     */
    public function orderedSections(Formation $formation): Collection
    {
        return $formation->sections()
            ->where('is_active', true)
            ->with([
                'chapters' => fn ($query) => $query->where('is_active', true)->orderBy('order_position'),
                'exam',
            ])
            ->get();
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

    public function latestChapter(User $user, Formation $formation): ?Chapter
    {
        $sections = $this->orderedSections($formation);
        $chapters = $sections
            ->flatMap(fn (Section $section) => $section->chapters)
            ->values();

        if ($chapters->isEmpty()) {
            return null;
        }

        $latestProgress = UserProgress::query()
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $chapters->pluck('id'))
            ->latest('updated_at')
            ->first();

        $completedChapterIds = $this->completedChapterIds($user, $formation);
        $sectionStates = $this->sectionStates($user, $formation)->keyBy('id');
        $latestChapter = $latestProgress
            ? $chapters->firstWhere('id', $latestProgress->trackable_id)
            : null;

        if ($latestChapter
            && in_array($latestChapter->section_id, $sectionStates->where('unlocked', true)->keys()->all(), true)
            && ! in_array($latestChapter->id, $completedChapterIds, true)) {
            return $latestChapter;
        }

        foreach ($sections as $section) {
            $state = $sectionStates->get($section->id);

            if (! ($state['unlocked'] ?? false)) {
                continue;
            }

            $nextIncompleteChapter = $section->chapters
                ->first(fn (Chapter $chapter): bool => ! in_array($chapter->id, $completedChapterIds, true));

            if ($nextIncompleteChapter) {
                return $nextIncompleteChapter;
            }
        }

        return $latestChapter ?? $chapters->first();
    }

    public function progressPercentage(User $user, Formation $formation): float
    {
        $sections = $this->orderedSections($formation);

        if ($sections->isEmpty()) {
            return 0.0;
        }

        $completedChapterIds = $this->completedChapterIds($user, $formation);
        $totalSteps = 0;
        $completedSteps = 0;

        foreach ($sections as $section) {
            $chapterIds = $section->chapters->pluck('id');
            $totalSteps += $chapterIds->count();
            $completedSteps += $chapterIds
                ->filter(fn (int $id): bool => in_array($id, $completedChapterIds, true))
                ->count();

            $totalSteps++;
            $sectionExam = $this->sectionExam($section);

            if ($sectionExam?->hasUserPassed($user)) {
                $completedSteps++;
            }
        }

        if ($formation->is_certifying) {
            $totalSteps++;

            if ($this->formationExam($formation)?->hasUserPassed($user)) {
                $completedSteps++;
            }
        }

        if ($totalSteps === 0) {
            return 0.0;
        }

        return round(($completedSteps / $totalSteps) * 100, 2);
    }

    public function syncProgress(User $user, Formation $formation): ?Enrollment
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->first();

        if (! $enrollment) {
            return null;
        }

        $enrollment->update([
            'progress_percentage' => $this->progressPercentage($user, $formation),
        ]);

        return $enrollment->refresh();
    }

    public function sectionExam(Section $section): ?Exam
    {
        $exam = $section->exam;

        return $exam && $exam->is_active ? $exam : null;
    }

    /**
     * Marks the enrollment as completed once the whole formation is finished and issues
     * a certificate only for a certifying formation whose final exam was passed.
     */
    public function syncCompletion(User $user, Formation $formation): ?Certificate
    {
        $this->syncProgress($user, $formation);

        if (! $this->isFormationComplete($user, $formation)) {
            return null;
        }

        $this->markEnrollmentCompleted($user, $formation);

        return $this->issueCertificate($user, $formation);
    }

    public function isFormationComplete(User $user, Formation $formation): bool
    {
        if (! $this->areSectionsComplete($user, $formation)) {
            return false;
        }

        if (! $formation->is_certifying) {
            return true;
        }

        $finalExam = $this->formationExam($formation);

        return $finalExam !== null && $finalExam->hasUserPassed($user);
    }

    public function areSectionsComplete(User $user, Formation $formation): bool
    {
        $completedChapterIds = $this->completedChapterIds($user, $formation);

        return $this->orderedSections($formation)
            ->every(fn (Section $section): bool => $this->isSectionComplete($user, $section, $completedChapterIds));
    }

    public function isSectionComplete(User $user, Section $section, ?array $completedChapterIds = null): bool
    {
        $completedChapterIds ??= $this->completedChapterIds($user, $section->formation);

        if (! $this->sectionChaptersComplete($section, $completedChapterIds)) {
            return false;
        }

        $exam = $this->sectionExam($section);

        return $exam !== null && $exam->hasUserPassed($user);
    }

    public function isSectionUnlocked(User $user, Section $section): bool
    {
        $section->loadMissing('formation');

        if (! $section->formation || ! $section->is_active) {
            return false;
        }

        $state = $this->sectionStates($user, $section->formation)
            ->firstWhere('id', $section->id);

        return (bool) ($state['unlocked'] ?? false);
    }

    /**
     * Best score obtained on the certification exam.
     */
    public function finalScore(User $user, Formation $formation): ?float
    {
        $exam = $this->formationExam($formation);
        if (! $exam) {
            return null;
        }

        $score = $exam->attempts()
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::COMPLETED->value)
            ->max('percentage');

        return $score === null ? null : round((float) $score, 2);
    }

    public function formationExam(Formation $formation): ?Exam
    {
        $exam = $formation->exam;

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
                'progress_percentage' => 100,
            ]);
        }
    }

    private function issueCertificate(User $user, Formation $formation): ?Certificate
    {
        if (! $formation->is_certifying) {
            return null;
        }

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

        return Certificate::query()
            ->create([
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'final_score' => $finalScore,
                'issue_date' => now(),
                'status' => CertificateStatusEnum::ACTIVE,
            ]);
    }
}
