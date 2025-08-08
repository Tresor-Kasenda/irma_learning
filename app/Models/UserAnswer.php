<?php

namespace App\Models;

use Database\Factories\UserAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    /** @use HasFactory<UserAnswerFactory> */
    use HasFactory;

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function checkCorrectness(): void
    {
        if ($this->question->question_type === 'single_choice' || $this->question->question_type === 'multiple_choice') {
            $this->is_correct = $this->selectedOption?->is_correct ?? false;
            $this->points_earned = $this->is_correct ? $this->question->points : 0;
        }
        $this->save();
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean'
        ];
    }
}
