<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Section $section) {
            // Set order_position automatically if not set
            if (empty($section->order_position)) {
                $maxPosition = static::where('formation_id', $section->formation_id)
                    ->max('order_position') ?? 0;
                $section->order_position = $maxPosition + 1;
            }

            // Calculate duration automatically if not set by user
            if (is_null($section->duration) && $section->formation_id) {
                $section->duration = self::calculateDuration($section->formation_id);
            }
        });

        static::updating(function (Section $section) {
            // Recalculate duration only if formation_id changed and duration was not manually modified
            if ($section->isDirty('formation_id') && !$section->isDirty('duration')) {
                $section->duration = self::calculateDuration($section->formation_id);
            }
        });
    }

    /**
     * Calculate section duration based on formation total duration
     * Distributes the formation's total duration evenly across all sections
     */
    protected static function calculateDuration(?int $formationId): int
    {
        if (!$formationId) {
            return 0;
        }

        // Use selectRaw for optimization - get formation duration and section count in one query
        $formation = Formation::where('id', $formationId)
            ->select('id', 'duration_hours')
            ->first();

        if (!$formation || !$formation->duration_hours) {
            return 0;
        }

        // Convert formation duration from hours to minutes
        $formationDurationMinutes = $formation->duration_hours * 60;

        // Get the current number of sections for this formation
        $existingSections = static::where('formation_id', $formationId)->count();

        // Total sections = existing + 1 (the new one being created)
        $totalSections = $existingSections + 1;

        // Calculate and return average duration per section
        return (int)round($formationDurationMinutes / $totalSections);
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order_position');
    }

    public function exam(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order_position' => 'integer',
            'duration' => 'integer'
        ];
    }
}
