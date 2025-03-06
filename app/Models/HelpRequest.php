<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HelpRequest extends Model
{
    /** @use HasFactory<\Database\Factories\HelpRequestFactory> */
    use HasFactory;

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class, 'problem_id');
    }
}
