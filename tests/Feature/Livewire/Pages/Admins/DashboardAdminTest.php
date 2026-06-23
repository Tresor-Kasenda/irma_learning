<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Livewire\Pages\Admins\DashboardAdmin;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    Livewire::actingAs($admin)
        ->test(DashboardAdmin::class)
        ->assertStatus(200);
});
