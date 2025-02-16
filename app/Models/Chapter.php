<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterProgressEnum;
use App\Observers\ChapterObserver;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(ChapterObserver::class)]
final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    public function cours(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class);
    }

    public function hasProgress(): bool
    {
        return $this->progress()->exists();
    }

    public function progress(): HasOne
    {
        return $this->hasOne(ChapterProgress::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(ExamSubmission::class);
    }

    public function isCompleted(): bool
    {
        return $this->progress()->where('status', ChapterProgressEnum::COMPLETED)->exists();
    }
}
