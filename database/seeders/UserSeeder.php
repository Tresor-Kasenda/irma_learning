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
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin IRMA',
            'username' => 'admin',
            'firstname' => 'Admin',
            'email' => 'admin@irmalearning.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::ADMIN->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 001',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Jean Mbele',
            'username' => 'jmbele',
            'firstname' => 'Jean',
            'email' => 'jean.mbele@irmalearning.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::INSTRUCTOR->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 002',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Sarah Nkwi',
            'username' => 'snkwi',
            'firstname' => 'Sarah',
            'email' => 'sarah.nkwi@irmalearning.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::INSTRUCTOR->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 003',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Paul Biya',
            'username' => 'pbstudent',
            'firstname' => 'Paul',
            'email' => 'paul.biya@email.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 004',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Marie Ebongue',
            'username' => 'mebongue',
            'firstname' => 'Marie',
            'email' => 'marie.ebongue@email.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 005',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Eric Kamga',
            'username' => 'ekamga',
            'firstname' => 'Eric',
            'email' => 'eric.kamga@email.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 006',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Alice Ndongo',
            'username' => 'andongo',
            'firstname' => 'Alice',
            'email' => 'alice.ndongo@email.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 007',
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'David Tchinda',
            'username' => 'dtchinda',
            'firstname' => 'David',
            'email' => 'david.tchinda@email.com',
            'password' => Hash::make('password'),
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'phone' => '+237 670 000 008',
            'email_verified_at' => now(),
        ]);
    }
}
