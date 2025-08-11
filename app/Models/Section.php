<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('order_position');
    }

    public function exam(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order_position' => 'int',
            'estimated_duration' => 'int'
        ];
    }
}
