<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ChapterObserver;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ChapterObserver::class)]
final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    public function cours(): BelongsTo
    {
        return $this->belongsTo(MasterClass::class, 'master_class_id');
    }

    // ajouter la relation entre chapter et chapterProgress
    

    public function previousChapter()
    {
        return static::where('master_class_id', $this->master_class_id)
            ->where('order_sequence', '>', $this->order_sequence)
            ->orderBy('order_sequence', 'asc')
            ->first();
    }

    public function nextChapter()
    {
        return static::where('master_class_id', $this->master_class_id)
            ->where('order_sequence', '>', $this->order_sequence)
            ->orderBy('position', 'asc')
            ->first();
    }
}
