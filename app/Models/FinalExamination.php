<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalExamination extends Model
{
    /** @use HasFactory<\Database\Factories\FinalExaminationFactory> */
    use HasFactory;

    public function masterClass(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'files' => 'array',
        ];
    }
}
