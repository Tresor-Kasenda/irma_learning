<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    public function examable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query): Model
    {
        return $query->where('is_active', true);
    }

    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
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

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function getPassingScore(): float
    {
        return $this->passing_score ?? 60;
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
