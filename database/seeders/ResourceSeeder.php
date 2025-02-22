<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

final class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Resource::factory()
            ->count(10)
            ->create();
    }
}
