<?php

namespace App\Models;

use App\Enums\QuestionTypeEnum;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_position');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'question_type' => QuestionTypeEnum::class,
            'points' => 'integer',
            'order_position' => 'integer',
        ];
    }
}
