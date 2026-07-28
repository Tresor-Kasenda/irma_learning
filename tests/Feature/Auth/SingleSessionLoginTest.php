<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedActiveSession(User $user, string $sessionId = 'other-device-session', ?int $lastActivity = null): void
{
    DB::table(config()->string('session.table', 'sessions'))->insert([
        'id' => $sessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Testing',
        'payload' => base64_encode(serialize([])),
        'last_activity' => $lastActivity ?? now()->getTimestamp(),
    ]);
}

test('a student cannot log in while another session is already active', function () {
    $user = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    seedActiveSession($user);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertInvalid(['email']);
    $this->assertGuest();
});

test('a student can log in again once the previous session has expired', function () {
    $user = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $expiredActivity = now()->subMinutes(config()->integer('session.lifetime') + 5)->getTimestamp();
    seedActiveSession($user, lastActivity: $expiredActivity);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('administrators are not limited to a single session', function () {
    $administrator = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    seedActiveSession($administrator);

    $response = $this->post('/login', [
        'email' => $administrator->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($administrator);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});
