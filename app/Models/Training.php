<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TrainingStatusEnum;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Training extends Model
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory;


    protected function casts(): array
    {
        return [
            'price' => 'float',
            'duration' => 'integer',
            'status' => TrainingStatusEnum::class,
        ];
    }
}
