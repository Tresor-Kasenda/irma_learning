<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MasterClassResourceEnum;
use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Resource extends Model
{
    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    public function courses(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    protected function casts(): array
    {
        return [
            'type' => MasterClassResourceEnum::class,
        ];
    }
}
