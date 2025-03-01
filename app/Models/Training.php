<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Training extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingFactory> */
    use HasFactory;

    public function cours(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
