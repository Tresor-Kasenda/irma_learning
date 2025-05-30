<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterProgressEnum;
use App\Observers\ChapterObserver;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

#[ObservedBy(ChapterObserver::class)]
final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    public function cours(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class);
    }

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class);
    }

    public function hasProgress(): bool
    {
        return $this->progress()
            ->where('user_id', '=', Auth::user()->id)
            ->exists();
    }

    public function progress(): HasOne
    {
        return $this->hasOne(ChapterProgress::class);
    }

    public function scopeSearchByActive(Builder $query, self $activeChapter): Builder
    {
        return $query->where('id', $activeChapter->id)
            ->orderBy('position');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function scopeGetChapterIndex(Builder $query, self $activeChapter): int
    {
        return $query
            ->pluck('id')->search($activeChapter->id);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(ExamSubmission::class);
    }

    public function isCompleted(): bool
    {
        $user = Auth::user();
        $hasCompleted = $this->progress()
            ->where('user_id', '=', $user->id)
            ->where('status', ChapterProgressEnum::COMPLETED->value)
            ->exists();

        $hasReferenceCode = ! empty($user->reference_code);

        return $hasCompleted && $hasReferenceCode;
    }

    protected function casts(): array
    {
        return [
            'is_final_chapter' => 'boolean',
            'points' => 'int',
        ];
    }
}
