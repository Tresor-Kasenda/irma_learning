<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterTypeEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\FormationLevelEnum;
use App\Services\CatalogStatsService;
use Database\Factories\FormationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'image',
        'price',
        'duration_hours',
        'difficulty_level',
        'is_active',
        'is_featured',
        'tags',
    ];

    /**
     * @return array<string, callable(Builder): Builder>
     */
    public static function catalogCountRelations(): array
    {
        return [
            'chapters as chapter_count' => fn (Builder $query): Builder => $query
                ->where('chapters.is_active', true),
            'chapters as video_count' => fn (Builder $query): Builder => $query
                ->where('chapters.is_active', true)
                ->where('content_type', ChapterTypeEnum::VIDEO->value),
            'chapters as pdf_count' => fn (Builder $query): Builder => $query
                ->where('chapters.is_active', true)
                ->where('content_type', ChapterTypeEnum::PDF->value),
            'chapters as text_count' => fn (Builder $query): Builder => $query
                ->where('chapters.is_active', true)
                ->where('content_type', ChapterTypeEnum::TEXT->value),
            'enrollments as students_count' => fn (Builder $query): Builder => $query
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ]),
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEstimatedDuration(): int
    {
        return $this->sections()->sum('duration');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_position');
    }

    public function chapters(): HasManyThrough
    {
        return $this->hasManyThrough(Chapter::class, Section::class);
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

    protected static function booted(): void
    {
        self::saved(fn (): null => self::flushCatalogStats());
        self::deleted(fn (): null => self::flushCatalogStats());
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

    private static function flushCatalogStats(): null
    {
        app(CatalogStatsService::class)->forget();

        return null;
    }
}
