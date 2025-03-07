<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create([
            'must_change_password' => true,
        ]);
        $this->call([
            EventTypeSeeder::class,
            EventSeeder::class,
            MasterClassSeeder::class,
            ChapterSeeder::class,
            ResourceSeeder::class,
            ExaminationSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
