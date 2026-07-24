<?php

declare(strict_types=1);

use App\Models\ApplicationSetting;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'test.user',
        'email' => 'test@example.com',
        'password' => 'MotDePasse!2026',
        'password_confirmation' => 'MotDePasse!2026',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas(User::class, [
        'name' => 'Test User',
        'username' => 'test.user',
        'email' => 'test@example.com',
    ]);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration requires a unique username and a compliant password', function () {
    User::factory()->create(['username' => 'tresor']);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Trésor Kasenda',
        'username' => 'tresor',
        'email' => 'tresor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasErrors(['username', 'password'])
        ->assertRedirect('/register');

    $this->assertGuest();
    $this->assertDatabaseMissing(User::class, ['email' => 'tresor@example.com']);
});

test('registration routes are unavailable when public registration is disabled', function () {
    ApplicationSetting::current()->update(['allow_registration' => false]);

    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Blocked User',
        'username' => 'blocked.user',
        'email' => 'blocked@example.com',
        'password' => 'MotDePasse!2026',
        'password_confirmation' => 'MotDePasse!2026',
    ])->assertNotFound();

    $this->assertGuest();
});
