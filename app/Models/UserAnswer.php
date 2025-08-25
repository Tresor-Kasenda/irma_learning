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

    protected $guarded = [];

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
        $isCorrect = false;
        $pointsEarned = 0;

        switch ($this->question->question_type->value) {
            case 'single_choice':
                $isCorrect = $this->selectedOption?->is_correct ?? false;
                $pointsEarned = $isCorrect ? $this->question->points : 0;
                break;

            case 'multiple_choice':
                $correctOptions = $this->question->options()->where('is_correct', true)->get();
                $selectedOptions = json_decode($this->selected_options ?? '[]', true);

                $correctSelected = collect($selectedOptions)->filter(function ($optionId) use ($correctOptions) {
                    return $correctOptions->contains('id', $optionId);
                });

                $incorrectSelected = collect($selectedOptions)->filter(function ($optionId) use ($correctOptions) {
                    return !$correctOptions->contains('id', $optionId);
                });

                if ($correctSelected->count() > 0 && $incorrectSelected->count() === 0) {
                    $percentage = $correctSelected->count() / $correctOptions->count();
                    $pointsEarned = (int)($this->question->points * $percentage);
                    $isCorrect = $percentage === 1.0;
                }
                break;
        }

        $this->update([
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean'
        ];
    }
}
