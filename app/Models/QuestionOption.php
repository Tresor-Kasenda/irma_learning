<?php

namespace App\Models;

use Database\Factories\QuestionOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    /** @use HasFactory<QuestionOptionFactory> */
    use HasFactory;

    protected $guarded = [];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'selected_option_id');
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_position' => 'integer',
        ];
    }
}
