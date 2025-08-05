<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MasterClassEnum;
use Database\Factories\MasterClassFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

final class MasterClass extends Model
{
    /** @use HasFactory<MasterClassFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'duration',
        'price',
        'status',
        'sub_title',
        'presentation',
        'path',
        'ended_at',
        'certifiable',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function finalExam(): HasOne
    {
        return $this->hasOne(FinalExamination::class);
    }

    public function examFinal(): HasOne
    {
        return $this->hasOne(ExamFinal::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'user_master_classe',
            'master_class_id',
            'user_id'
        )
            ->withPivot('reference_code')
            ->withTimestamps();
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    // Scopes for better query performance
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', MasterClassEnum::PUBLISHED);
    }

    public function scopeWithEssentials(Builder $query): Builder
    {
        return $query->with(['chapters', 'resources', 'subscriptions']);
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('price')->orWhere('price', '<=', 0);
        });
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('price', '>', 0);
    }

    // Helper methods
    public function isFree(): bool
    {
        return $this->price === null || $this->price <= 0;
    }

    public function isPublished(): bool
    {
        return $this->status === MasterClassEnum::PUBLISHED->value;
    }

    public function getCompletionRate(): float
    {
        $totalChapters = $this->chapters()->count();
        if ($totalChapters === 0) return 0;

        $completedSubscriptions = $this->subscriptions()
            ->where('status', 'completed')
            ->count();

        $totalSubscriptions = $this->subscriptions()->count();
        
        return $totalSubscriptions > 0 ? ($completedSubscriptions / $totalSubscriptions) * 100 : 0;
    }

    public function getAverageProgress(): float
    {
        return $this->subscriptions()->avg('progress') ?? 0;
    }

    protected function casts(): array
    {
        return [
            'ended_at' => 'datetime',
            'certifiable' => 'boolean',
            'status' => MasterClassEnum::class,
            'price' => 'decimal:2',
        ];
    }
}
