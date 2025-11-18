<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FormationLevelEnum;
use Database\Factories\FormationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

final class Formation extends Model
{
    /** @use HasFactory<FormationFactory> */
    use HasFactory;

    use HasSlug;
    use LogsActivity;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function exam(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function exams(): MorphMany
    {
        return $this->morphMany(Exam::class, 'examable');
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

    public function getEstimatedDuration(): int
    {
        return $this->sections()->sum('duration');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_position');
    }

    public function getEnrollmentCount(): int
    {
        return $this->enrollments()
            ->where('status', '=', 'paid')
            ->count();
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
            'duration_hours' => 'integer',
        ];
    }
}
