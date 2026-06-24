<?php

namespace App\Http\Controllers\Dashboard\Learnings;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\UserProgress;
use Inertia\Inertia;

class StudentLearningPlayController extends Controller
{
    public function __invoke(Formation $formation)
    {
        $user = auth()->user();

        $formation->load(['sections.chapters' => function ($query) {
            $query->where('is_active', true)
                ->with(['exams' => fn($query) => $query->active()])
                ->orderBy('order_position');
        }]);

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->whereIn('status', [EnrollmentStatusEnum::ACTIVE, EnrollmentStatusEnum::COMPLETED])
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.learnings.detail', $formation->slug)
                ->with('error', 'Vous devez être inscrit à cette formation pour y accéder.');
        }

        $allChapters = $formation->sections
            ->flatMap(fn($section) => $section->chapters)
            ->values();

        $chapterId = request()->query('chapterId');
        $currentChapter = null;

        if ($chapterId) {
            $currentChapter = $allChapters->firstWhere('id', (int)$chapterId);
        } else {
            $lastProgress = UserProgress::where('user_id', $user->id)
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', $allChapters->pluck('id'))
                ->where('status', UserProgressEnum::IN_PROGRESS)
                ->latest('updated_at')
                ->first();

            if ($lastProgress) {
                $currentChapter = $allChapters->firstWhere('id', $lastProgress->trackable_id);
            }
        }

        if (!$currentChapter) {
            $currentChapter = $allChapters->first();
        }

        $currentChapterPosition = $currentChapter
            ? $allChapters->search(fn($chapter) => $chapter->id === $currentChapter->id)
            : false;
        $currentChapterIndex = $currentChapterPosition === false ? 0 : $currentChapterPosition;

        if ($currentChapter && !UserProgress::where([
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

        $chapterExam = $currentChapter?->exams()->active()->first();
        $hasPassedExam = true;
        if ($chapterExam) {
            $hasPassedExam = $chapterExam->hasUserPassed($user);
        }
        return Inertia::render('Dashboard/Learnings/StudentLearningPlay', [
            'formation' => $formation,
            'enrollment' => $enrollment,
            'allChapters' => $allChapters->values()->toArray(),
            'currentChapter' => $currentChapter,
            'currentChapterIndex' => $currentChapterIndex,
            'completedChapters' => $completedChapters,
            'htmlContent' => $currentChapter?->getHtmlContent() ?? '',
            'chapterExam' => $chapterExam,
            'hasPassedExam' => $hasPassedExam,
        ]);
    }

    public function detailCourse(Formation $formation)
    {
        return Inertia::render('Dashboard/Learnings/CourseDetail', []);
    }
}
