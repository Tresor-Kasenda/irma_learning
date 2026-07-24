<?php

declare(strict_types=1);

use App\Models\ApplicationSetting;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'http://localhost/auth/google/callback',
        'services.github.client_id' => 'github-client-id',
        'services.github.client_secret' => 'github-client-secret',
        'services.github.redirect' => 'http://localhost/auth/github/callback',
    ]);
});

test('users can start Google authentication', function () {
    Socialite::fake('google');

    $response = $this->get(route('social.redirect', ['provider' => 'google']));

    $response->assertRedirect('https://socialite.fake/google/authorize');
});

test('Google authentication creates a verified learner account when it does not exist', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-user-123',
        'email' => 'tresor@example.com',
        'email_verified' => true,
        'name' => 'Trésor Kasenda',
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $user = User::query()->sole();

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => 'Trésor Kasenda',
        'username' => 'tresor',
        'email' => 'tresor@example.com',
        'google_id' => 'google-user-123',
    ]);
    expect($user->email_verified_at)->not->toBeNull();
});

test('Google authentication links a local account with the same verified email address', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
        'google_id' => null,
    ]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-user-456',
        'email' => $user->email,
        'email_verified' => true,
        'name' => $user->name,
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->google_id)->toBe('google-user-456');
});

test('Google authentication rejects an unverified email address', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-user-789',
        'email' => 'unverified@example.com',
        'email_verified' => false,
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing(User::class, ['email' => 'unverified@example.com']);
});

test('Google authentication handles provider errors', function () {
    Socialite::fake('google', static function (): never {
        throw new RuntimeException('Google is unreachable.');
    });

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('Google authentication does not create an account while registrations are closed', function () {
    ApplicationSetting::current()->update(['allow_registration' => false]);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-user-999',
        'email' => 'new-user@example.com',
        'email_verified' => true,
        'name' => 'New User',
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing(User::class, ['email' => 'new-user@example.com']);
});

test('users can start GitHub authentication', function () {
    Socialite::fake('github');

    $response = $this->get(route('social.redirect', ['provider' => 'github']));

    $response->assertRedirect('https://socialite.fake/github/authorize');
});

test('GitHub authentication creates a verified learner account when it does not exist', function () {
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-user-123',
        'email' => 'tresor.github@example.com',
        'name' => 'Trésor Kasenda',
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $user = User::query()->sole();

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => 'Trésor Kasenda',
        'username' => 'tresor.github',
        'email' => 'tresor.github@example.com',
        'github_id' => 'github-user-123',
    ]);
    expect($user->email_verified_at)->not->toBeNull();
});

test('GitHub authentication links a local account with the same verified email address', function () {
    $user = User::factory()->create([
        'email' => 'existing.github@example.com',
        'github_id' => null,
    ]);

    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-user-456',
        'email' => $user->email,
        'name' => $user->name,
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->github_id)->toBe('github-user-456');
});

test('GitHub authentication rejects accounts without a verified email address', function () {
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-user-789',
        'email' => null,
    ]));

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $response
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing(User::class, ['github_id' => 'github-user-789']);
});
