<?php

namespace App\Service;

use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ProgressTrackingService
{
    public function completeChapter(Subscription $subscription, Chapter $chapter)
    {
        DB::transaction(function () use ($subscription, $chapter) {
            // Check if the previous chapter is completed
            $previousChapter = $chapter->previousChapter();
            if ($previousChapter && !$this->isChapterCompleted($subscription, $previousChapter)) {
                throw new \Exception('Previous chapter must be completed first.');
            }

            // Mark the chapter as completed
            ChapterProgress::updateOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'chapter_id' => $chapter->id
                ],
                [
                    'status' => 'completed',
                    'points_earned' => $chapter->points,
                    'completed_at' => now()
                ]
            );

            // Update overall progress
            $this->updateOverallProgress($subscription);
        });

        return $this->getDetailedProgress($subscription);
    }

    protected function isChapterCompleted(Subscription $subscription, Chapter $chapter)
    {
        return $subscription->chapterProgress()
            ->where('chapter_id', $chapter->id)
            ->where('status', 'completed')
            ->exists();
    }

    protected function updateOverallProgress(Subscription $subscription)
    {
        $progress = $this->getDetailedProgress($subscription);

        $subscription->update([
            'progress' => $progress['progress_percentage'],
            'completed_at' => $progress['progress_percentage'] == 100 ? now() : null
        ]);
    }

    public function getDetailedProgress(Subscription $subscription): array
    {
        $masterClass = $subscription->masterClass;
        $totalChapters = $masterClass->chapters()->count();
        $completedChapters = $subscription->chapterProgress()
            ->where('status', 'completed')
            ->count();

        $totalPoints = $masterClass->chapters()->sum('points');
        $earnedPoints = $subscription->chapterProgress()
            ->join('chapters', 'chapters.id', '=', 'chapter_progress.chapter_id')
            ->where('status', 'completed')
            ->sum('points');

        $chapterProgress = $masterClass->chapters()
            ->leftJoin('chapter_progress', function ($join) use ($subscription) {
                $join->on('chapters.id', '=', 'chapter_progress.chapter_id')
                    ->where('chapter_progress.subscription_id', '=', $subscription->id);
            })
            ->select([
                'chapters.id',
                'chapters.title',
                'chapters.points',
                'chapter_progress.status',
                'chapter_progress.completed_at'
            ])
            ->get();

        return [
            'total_chapters' => $totalChapters,
            'completed_chapters' => $completedChapters,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'progress_percentage' => ($totalChapters > 0)
                ? round(($completedChapters / $totalChapters) * 100, 2)
                : 0,
            'points_percentage' => ($totalPoints > 0)
                ? round(($earnedPoints / $totalPoints) * 100, 2)
                : 0,
            'chapter_details' => $chapterProgress,
            'can_take_exam' => $this->canTakeExam($subscription)
        ];
    }

    public function canTakeExam(Subscription $subscription): bool
    {
        $progress = $this->getDetailedProgress($subscription);

        // Check if the student has completed at least 80% of the course
        $minimumProgress = 80;
        return $progress['progress_percentage'] >= $minimumProgress;
    }
}
