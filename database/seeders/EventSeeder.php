<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

final class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()
            ->count(10)
            ->create();
    }
}
