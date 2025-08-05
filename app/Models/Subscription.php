<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionEnum;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'master_class_id',
        'status',
        'progress',
        'started_at',
        'completed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function masterClass(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    public function chapterProgress(): HasMany
    {
        return $this->hasMany(ChapterProgress::class, 'subscription_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionEnum::ACTIVE);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', SubscriptionEnum::COMPLETED);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', SubscriptionEnum::EXPIRED);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === SubscriptionEnum::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === SubscriptionEnum::COMPLETED;
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionEnum::EXPIRED;
    }

    public function getProgressPercentage(): float
    {
        return round($this->progress, 2);
    }

    public function getDuration(): ?int
    {
        if (!$this->completed_at || !$this->started_at) {
            return null;
        }

        return (int) $this->started_at->diffInDays($this->completed_at);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress' => 'float',
            'status' => SubscriptionEnum::class,
        ];
    }
}
