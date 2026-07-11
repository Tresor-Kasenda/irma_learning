<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsAllActivity;
use Database\Factories\QuestionOptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuestionOption extends Model
{
    /** @use HasFactory<QuestionOptionFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'order_position',
        'explanation',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'selected_option_id');
    }

    protected static function booted(): void
    {
        self::deleting(function (QuestionOption $option): void {
            $option->userAnswers()->update(['selected_option_id' => null]);
        });
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_position' => 'integer',
        ];
    }
}
