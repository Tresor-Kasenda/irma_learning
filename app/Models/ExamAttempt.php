<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExamAttemptEnum;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'completed_at',
        'score',
        'max_score',
        'percentage',
        'status',
        'answers',
        'question_order',
        'attempt_number',
        'time_taken',
    ];

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
        $userAnswers = $this->userAnswers()->with('question.options')->get();

        foreach ($userAnswers as $userAnswer) {
            $userAnswer->checkCorrectness();
        }

        $totalScore = $userAnswers->sum('points_earned');
        $maxScore = $this->exam->getTotalPoints();
        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        $status = $percentage >= ($this->exam->passing_score ?? 70)
            ? ExamAttemptEnum::COMPLETED
            : ExamAttemptEnum::FAILED;

        $this->update([
            'status' => $status,
            'completed_at' => now(),
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'time_taken' => now()->diffInSeconds($this->started_at),
        ]);

        if ($this->isPassed() && $this->exam->examable_type === 'App\Models\Chapter') {
            $chapter = $this->exam->examable;
            UserProgress::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'trackable_type' => 'App\Models\Chapter',
                    'trackable_id' => $chapter->id,
                ],
                [
                    'progress_percentage' => 100,
                    'status' => \App\Enums\UserProgressEnum::COMPLETED,
                    'completed_at' => now(),
                ]
            );
        }
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function isPassed(): bool
    {
        return (float) $this->percentage >= (float) ($this->exam->passing_score ?? 70);
    }

    protected static function booted(): void
    {
        self::creating(function (self $attempt): void {
            if (empty($attempt->attempt_number)) {
                $attempt->attempt_number = static::where('exam_id', $attempt->exam_id)
                    ->where('user_id', $attempt->user_id)
                    ->max('attempt_number') + 1;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'question_order' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'percentage' => 'decimal:2',
            'attempt_number' => 'integer',
            'status' => ExamAttemptEnum::class,
            'score' => 'integer',
            'max_score' => 'integer',
            'time_taken' => 'integer',
        ];
    }
}
