<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterTypeEnum;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Chapter $chapter) {
            if (empty($chapter->order_position)) {
                // Calculer automatiquement la prochaine position dans cette section
                $maxPosition = static::where('section_id', $chapter->section_id)
                    ->max('order_position') ?? 0;
                $chapter->order_position = $maxPosition + 1;
            }
        });
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function exam(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query): Model|Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFree($query): Model|Builder
    {
        return $query->where('is_free', true);
    }

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
            'content_type' => ChapterTypeEnum::class,
        ];
    }
}
