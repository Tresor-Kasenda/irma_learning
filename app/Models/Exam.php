<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExamAttemptEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Concerns\LogsAllActivity;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'examable_type',
        'examable_id',
        'title',
        'description',
        'instructions',
        'duration_minutes',
        'passing_score',
        'max_attempts',
        'randomize_questions',
        'show_results_immediately',
        'is_active',
        'available_from',
        'available_until',
    ];

    public function examable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
    }

    public function isReadyForPublication(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $this->loadMissing('questions.options');

        if ($this->questions->isEmpty()) {
            return false;
        }

        return $this->questions->every(function (Question $question): bool {
            if (mb_trim($question->question_text) === '' || $question->points < 1) {
                return false;
            }

            $optionCount = $question->options->count();
            $correctCount = $question->options->where('is_correct', true)->count();

            return match ($question->question_type) {
                QuestionTypeEnum::TRUE_FALSE => $optionCount === 2 && $correctCount === 1,
                QuestionTypeEnum::SINGLE_CHOICE => $optionCount >= 2 && $correctCount === 1,
                QuestionTypeEnum::MULTIPLE_CHOICE => $optionCount >= 2 && $correctCount >= 1,
            };
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_position');
    }

    public function canUserAttempt(User $user): bool
    {
        if ($this->max_attempts === 0) {
            return true;
        }

        return $this->getUserAttemptCount($user) < $this->max_attempts;
    }

    public function getUserAttemptCount(User $user): int
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->count();
    }

    public function hasUserPassed(User $user): bool
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::COMPLETED->value)
            ->where('percentage', '>=', $this->getPassingScore())
            ->exists();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getPassingScore(): float
    {
        return $this->passing_score ?? 70;
    }

    public function getFormationTitleAttribute(): ?string
    {
        if (! $this->examable) {
            return null;
        }

        if ($this->examable_type === 'App\Models\Formation') {
            return '📚 '.$this->examable->title;
        }

        if ($this->examable_type === 'App\Models\Section') {
            $this->examable->loadMissing('formation');

            return '📚 '.($this->examable->formation?->title ?? 'Formation inconnue');
        }

        if ($this->examable_type === 'App\Models\Chapter') {
            $this->examable->loadMissing('section.formation');

            return '📚 '.($this->examable->section?->formation?->title ?? 'Formation inconnue');
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'randomize_questions' => 'boolean',
            'show_results_immediately' => 'boolean',
            'is_active' => 'boolean',
            'duration_minutes' => 'integer',
            'passing_score' => 'integer',
            'max_attempts' => 'integer',
        ];
    }
}
