<?php

namespace App\Models;

use Database\Factories\ExamFinalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamFinal extends Model
{
    /** @use HasFactory<ExamFinalFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function masterClass(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }
}
