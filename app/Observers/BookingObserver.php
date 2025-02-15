<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingUpdatedNotification;
use App\Notifications\EventBookingNotification;

final class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $user = User::query()
            ->create([
                'name' => $booking->name,
                'email' => $booking->email,
                'password' => bcrypt($booking->reference),
                'role' => 'STUDENT',
            ]);

        \Illuminate\Support\defer(function () use ($booking, $user) {
            $user->notify(new EventBookingNotification($booking));
        });
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        $user = User::query()
            ->where('email', $booking->email)
            ->first();

        if ($booking->status) {
            $user->notify(new BookingUpdatedNotification($booking));
        }
    }
}
