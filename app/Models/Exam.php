<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

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
