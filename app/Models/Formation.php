<?php

namespace App\Models;

use App\Enums\FormationLevelEnum;
use Database\Factories\FormationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Formation extends Model
{
    /** @use HasFactory<FormationFactory> */
    use HasFactory;
    use LogsActivity;
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exam(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['status', 'payment_status', 'progress_percentage'])
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query): Model
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query): Model
    {
        return $query->where('is_featured', true);
    }

    public function scopeByDifficulty($query, $level): Model
    {
        return $query->where('difficulty_level', $level);
    }

    public function getTotalChaptersCount(): int
    {
        return $this->modules()
            ->with('sections.chapters')
            ->get()
            ->sum(function ($module) {
                return $module->sections->sum(function ($section) {
                    return $section->chapters->count();
                });
            });
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order_position');
    }

    public function getEstimatedDuration(): int
    {
        return $this->modules()->sum('estimated_duration');
    }

    public function getEnrollmentCount(): int
    {
        return $this->enrollments()->where('payment_status', 'paid')->count();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'difficulty_level' => FormationLevelEnum::class,
            'tags' => 'array',
            'price' => 'decimal:2',
            'certification_threshold' => 'integer'
        ];
    }
}
