<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('administrators are redirected to the administration dashboard after login', function () {
    $administrator = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $response = $this->post('/login', [
        'email' => $administrator->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($administrator);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('root users are redirected to the administration dashboard after login', function () {
    $root = User::factory()->create(['role' => UserRoleEnum::ROOT]);

    $response = $this->post('/login', [
        'email' => $root->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($root);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
