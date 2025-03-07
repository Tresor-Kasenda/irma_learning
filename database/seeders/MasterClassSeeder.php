<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MasterClass;
use Illuminate\Database\Seeder;

final class MasterClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MasterClass::factory()
            ->count(10)
            ->create();
    }
}
