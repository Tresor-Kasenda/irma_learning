<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExamAttemptEnum;
use App\Models\Concerns\LogsAllActivity;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'user_id',
        'exam_id',
        'started_at',
        'expires_at',
        'last_activity_at',
        'reopened_at',
        'reopened_by',
        'reopen_count',
        'completed_at',
        'score',
        'max_score',
        'percentage',
        'status',
        'answers',
        'question_order',
        'option_order',
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
        if ($this->status !== ExamAttemptEnum::IN_PROGRESS) {
            return;
        }

        if ($this->hasExpired()) {
            $this->expire();

            return;
        }

        $userAnswers = $this->userAnswers()->with(['question.options', 'selectedOption'])->get();

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
            'time_taken' => (int) max(0, $this->started_at->diffInSeconds(now())),
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

    public function hasExpired(): bool
    {
        return $this->status === ExamAttemptEnum::IN_PROGRESS
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function expire(): void
    {
        if ($this->status !== ExamAttemptEnum::IN_PROGRESS) {
            return;
        }

        $this->update([
            'status' => ExamAttemptEnum::EXPIRED,
            'completed_at' => now(),
            'time_taken' => (int) max(0, $this->started_at->diffInSeconds(now())),
        ]);
    }

    public function reopen(User $admin): bool
    {
        if (! in_array($this->status, [
            ExamAttemptEnum::EXPIRED,
            ExamAttemptEnum::FAILED,
            ExamAttemptEnum::CANCELLED,
        ], true)) {
            return false;
        }

        $duration = max((int) $this->exam->duration_minutes, 1);

        $this->update([
            'status' => ExamAttemptEnum::IN_PROGRESS,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($duration),
            'last_activity_at' => now(),
            'completed_at' => null,
            'score' => 0,
            'max_score' => $this->exam->getTotalPoints(),
            'percentage' => 0,
            'time_taken' => null,
            'reopened_at' => now(),
            'reopened_by' => $admin->id,
            'reopen_count' => $this->reopen_count + 1,
        ]);

        return true;
    }

    public function recordActivity(): void
    {
        $this->forceFill(['last_activity_at' => now()])->save();
    }

    protected static function booted(): void
    {
        self::creating(function (self $attempt): void {
            if (empty($attempt->attempt_number)) {
                $attempt->attempt_number = static::where('exam_id', $attempt->exam_id)
                    ->where('user_id', $attempt->user_id)
                    ->max('attempt_number') + 1;
            }

            $attempt->started_at ??= now();
            $attempt->last_activity_at ??= now();

            if ($attempt->expires_at === null && $attempt->exam_id) {
                $duration = (int) ($attempt->exam?->duration_minutes ?? 0);
                $attempt->expires_at = $duration > 0 ? $attempt->started_at->copy()->addMinutes($duration) : null;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'question_order' => 'array',
            'option_order' => 'array',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'reopened_at' => 'datetime',
            'completed_at' => 'datetime',
            'percentage' => 'decimal:2',
            'attempt_number' => 'integer',
            'status' => ExamAttemptEnum::class,
            'score' => 'integer',
            'max_score' => 'integer',
            'time_taken' => 'integer',
            'reopened_by' => 'integer',
            'reopen_count' => 'integer',
        ];
    }
}
