<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()
            ->create([
                'name' => 'admin',
                'username' => 'admin',
                'email' => 'admin@irmalearning.com',
                'password' => Hash::make('password'),
                'role' => UserRoleEnum::ADMIN->value,
                'status' => UserStatusEnum::ACTIVE->value,
            ]);
    }
}
