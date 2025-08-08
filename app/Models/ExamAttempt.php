<?php

namespace App\Models;

use App\Enums\ExamAttemptEnum;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePassed($query)
    {
        return $query->where('status', 'completed')
            ->whereColumn('percentage', '>=', 'exams.passing_score');
    }

    public function complete(): void
    {
        $this->calculateScore();
        $this->update([
            'status' => $this->isPassed() ? 'completed' : 'failed',
            'completed_at' => now(),
            'time_taken' => now()->diffInSeconds($this->started_at)
        ]);
    }

    public function calculateScore(): void
    {
        $totalScore = $this->userAnswers()->sum('points_earned');
        $maxScore = $this->exam->getTotalPoints();

        $this->update([
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0
        ]);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function isPassed(): bool
    {
        return $this->percentage >= $this->exam->passing_score;
    }

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'percentage' => 'decimal:2',
            'attempt_number' => 'integer',
            'status' => ExamAttemptEnum::class,
            'score' => 'integer',
            'max_score' => 'integer',
            'time_taken' => 'integer'
        ];
    }
}
