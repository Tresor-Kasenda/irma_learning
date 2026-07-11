<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserProgressEnum;
use App\Models\Concerns\LogsAllActivity;
use Database\Factories\UserProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class UserProgress extends Model
{
    /** @use HasFactory<UserProgressFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCompleted($query): Model
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query): Model
    {
        return $query->where('status', 'in_progress');
    }

    public function markAsStarted(): void
    {
        if ($this->status === UserProgressEnum::NOT_STARTED) {
            $this->update([
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        if ($this->trackable instanceof Chapter) {
            $formation = $this->trackable->section->formation;
            $enrollment = Enrollment::query()
                ->where('user_id', '=', $this->user_id)
                ->where('formation_id', '=', $formation->id)
                ->first();
            $enrollment?->updateProgress();
        }
    }

    protected function casts(): array
    {
        return [
            'progress_percentage' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'time_spent' => 'integer',
            'status' => UserProgressEnum::class,
        ];
    }
}
