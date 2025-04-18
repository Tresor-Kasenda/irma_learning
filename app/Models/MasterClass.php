<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MasterClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class MasterClass extends Model
{
    /** @use HasFactory<MasterClassFactory> */
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    public function finalExam(): HasOne
    {
        return $this->hasOne(FinalExamination::class);
    }

    public function examFinal(): HasOne
    {
        return $this->hasOne(ExamFinal::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'user_master_classe',
            'master_class_id',
            'user_id'
        )
            ->withPivot('reference_code')
            ->withTimestamps();
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    protected function casts(): array
    {
        return [
            'ended_at' => 'datetime',
            'certifiable' => 'boolean',
        ];
    }
}
