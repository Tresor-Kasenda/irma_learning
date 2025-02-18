<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Chapter;

final class ChapterObserver
{
    /**
     * Handle the Chapter "created" event.
     */
    public function created(Chapter $chapter): void {}

    /**
     * Handle the Chapter "updated" event.
     */
    public function updated(Chapter $chapter): void {}
}
