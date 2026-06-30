<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from the admin', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('a student cannot access the admin', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('an admin can access the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats.formations')
            ->has('stats.revenue')
            ->has('catalogStats.formations')
            ->etc());
});

test('a root user can access the admin dashboard', function () {
    $root = User::factory()->create(['role' => 'root']);

    $this->actingAs($root)
        ->get(route('admin.dashboard'))
        ->assertSuccessful();
});
