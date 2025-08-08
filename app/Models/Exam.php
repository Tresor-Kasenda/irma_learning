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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeActive($query): Model
    {
        return $query->where('is_active', true);
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
