<?php

namespace App\Models;

use App\Enums\ChapterProgressEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterProgress extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterProgressFactory> */
    use HasFactory;


    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === ChapterProgressEnum::COMPLETED;
    }

    protected function casts(): array
    {
        return [
            'status' => ChapterProgressEnum::class,
            'completed_at' => 'datetime',
            'points_earned' => 'integer'
        ];
    }
}
