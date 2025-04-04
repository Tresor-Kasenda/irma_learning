<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UserRoleEnum;
use App\Models\Booking;
use App\Models\User;
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
                'role' => UserRoleEnum::STUDENT->value,
                'must_change_password' => true,
                'reference_code' => $booking->reference,
                'phone' => $booking->phone_number,
                'firstname' => $booking->firstname
            ]);

        \Illuminate\Support\defer(function () use ($booking, $user) {
            $user->notify(new EventBookingNotification($booking));
        });
    }
}
