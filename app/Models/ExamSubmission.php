<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExamSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ExamSubmission extends Model
{
    /** @use HasFactory<ExamSubmissionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(ExamResult::class, 'exam_submission_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }
}
