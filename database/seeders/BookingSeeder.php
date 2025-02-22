<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

final class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Booking::factory()
            ->count(10)
            ->create();
    }
}
