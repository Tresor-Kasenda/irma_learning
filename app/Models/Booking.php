<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\BookingObserver;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(BookingObserver::class)]
final class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
