<?php

namespace App\Models;

use App\Enums\EnrollmentPaymentEnum;
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

    public function scopeActive($query)
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

    public function getTotalSectionsCount(): int
    {
        return $this->modules()
            ->with('sections')
            ->get()
            ->sum(function ($module) {
                return $module->sections->count();
            });
    }

    public function getCompletedSectionsCount(User $user): int
    {
        return UserProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereHasMorph('trackable', [Section::class], function ($query) {
                $query->whereHas('module', function ($q) {
                    $q->where('formation_id', $this->id);
                });
            })
            ->count();
    }

    public function getCompletedModulesCount(User $user): int
    {
        $totalModules = $this->modules->count();
        $completedCount = 0;

        foreach ($this->modules as $module) {
            $totalSections = $module->sections->count();
            $completedSections = UserProgress::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereHasMorph('trackable', [Section::class], function ($query) use ($module) {
                    $query->where('module_id', $module->id);
                })
                ->count();

            if ($completedSections >= $totalSections) {
                $completedCount++;
            }
        }

        return $completedCount;
    }

    public function getCertifiedStudentsCount(): int
    {
        return Certificate::where('formation_id', $this->id)
            ->where('status', 'active')
            ->count();
    }

    public function getPaidEnrollmentsCount(): int
    {
        return $this->enrollments()
            ->where('payment_status', EnrollmentPaymentEnum::PAID)
            ->count();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getEstimatedDuration(): int
    {
        return $this->modules()->sum('estimated_duration');
    }

    public function getEnrollmentCount(): int
    {
        return $this->enrollments()->where('payment_status', 'paid')->count();
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
