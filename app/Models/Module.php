<?php

namespace App\Models;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory;

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
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

    public function getChaptersCount(): int
    {
        return $this->sections()->with('chapters')->get()->sum(function ($section) {
            return $section->chapters->count();
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order_position');
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
