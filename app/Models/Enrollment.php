<?php

namespace App\Models;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;
    use LogsActivity;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }

    public function scopeActive($query): Model
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query): Model
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeCompleted($query): Model
    {
        return $query->where('status', 'completed');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function updateProgress(): void
    {
        $totalChapters = $this->formation->getTotalChaptersCount();
        $completedChapters = UserProgress::where('user_id', $this->user_id)
            ->where('status', EnrollmentStatusEnum::Completed->value)
            ->whereHasMorph('trackable', [Chapter::class], function ($query) {
                $query->whereHas('section.module', function ($q) {
                    $q->where('formation_id', $this->formation_id);
                });
            })
            ->count();

        $this->progress_percentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;
        $this->last_accessed_at = now();

        if ($this->progress_percentage >= 100) {
            $this->status = EnrollmentStatusEnum::Completed->value;
            $this->completion_date = now();
        }

        $this->save();
    }


    protected function casts(): array
    {
        return [
            'enrollment_date' => 'datetime',
            'completion_date' => 'datetime',
            'last_accessed_at' => 'datetime',
            'amount_paid' => 'decimal:2',
            'progress_percentage' => 'decimal:2',
            'status' => EnrollmentStatusEnum::class,
            'payment_status' => EnrollmentPaymentEnum::class
        ];
    }
}
