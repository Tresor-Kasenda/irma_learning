<?php

namespace App\Models;

use App\Enums\MasterClassResourceEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceFactory> */
    use HasFactory;


    public function courses(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    protected function casts(): array
    {
        return [
            'type' => MasterClassResourceEnum::class
        ];
    }
}
