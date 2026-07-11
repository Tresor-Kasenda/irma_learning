<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('an administrator can list and update non-root users', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data')
            ->where('canManageRoot', false));

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $student), [
            'role' => UserRoleEnum::INSTRUCTOR->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => true,
        ])
        ->assertRedirect();

    expect($student->refresh()->role)->toBe(UserRoleEnum::INSTRUCTOR)
        ->and($student->must_change_password)->toBeTrue();
});

test('an administrator cannot modify a root account', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $root = User::factory()->create(['role' => UserRoleEnum::ROOT]);

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $root), [
            'role' => UserRoleEnum::ADMIN->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => false,
        ])
        ->assertForbidden();

    expect($root->refresh()->role)->toBe(UserRoleEnum::ROOT);
});

test('a non-root administrator cannot promote a user to root', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $student), [
            'role' => UserRoleEnum::ROOT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => false,
        ])
        ->assertInvalid(['role']);

    expect($student->refresh()->role)->toBe(UserRoleEnum::STUDENT);
});

test('a root administrator can promote a user to root', function () {
    $root = User::factory()->create(['role' => UserRoleEnum::ROOT]);
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($root)
        ->patch(route('admin.users.update', $student), [
            'role' => UserRoleEnum::ROOT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => false,
        ])
        ->assertRedirect();

    expect($student->refresh()->role)->toBe(UserRoleEnum::ROOT);
});

test('an administrator can create a user with a role', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Nouvel Instructeur',
            'email' => 'instructor@irma.test',
            'password' => 'super-secret-1',
            'password_confirmation' => 'super-secret-1',
            'role' => UserRoleEnum::INSTRUCTOR->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => true,
        ])
        ->assertRedirect();

    $user = User::query()->where('email', 'instructor@irma.test')->firstOrFail();
    expect($user->role)->toBe(UserRoleEnum::INSTRUCTOR)
        ->and($user->must_change_password)->toBeTrue()
        ->and(Hash::check('super-secret-1', $user->password))->toBeTrue();
});

test('a non-root administrator cannot create a root user', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Faux Root',
            'email' => 'fakeroot@irma.test',
            'password' => 'super-secret-1',
            'password_confirmation' => 'super-secret-1',
            'role' => UserRoleEnum::ROOT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => true,
        ])
        ->assertInvalid(['role']);

    expect(User::query()->where('email', 'fakeroot@irma.test')->exists())->toBeFalse();
});

test('a root administrator can create a root user', function () {
    $root = User::factory()->create(['role' => UserRoleEnum::ROOT]);

    $this->actingAs($root)
        ->post(route('admin.users.store'), [
            'name' => 'Second Root',
            'email' => 'secondroot@irma.test',
            'password' => 'super-secret-1',
            'password_confirmation' => 'super-secret-1',
            'role' => UserRoleEnum::ROOT->value,
            'status' => UserStatusEnum::ACTIVE->value,
            'must_change_password' => false,
        ])
        ->assertRedirect();

    expect(User::query()->where('email', 'secondroot@irma.test')->first()?->role)->toBe(UserRoleEnum::ROOT);
});

test('a student cannot access user administration', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('an administrator can suspend and delete another user', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $student), [
            'role' => UserRoleEnum::STUDENT->value,
            'status' => UserStatusEnum::BANNED->value,
            'must_change_password' => false,
        ])
        ->assertRedirect();

    expect($student->refresh()->status)->toBe(UserStatusEnum::BANNED);

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $student))
        ->assertRedirect();

    expect($student->fresh())->toBeNull();
});

test('an administrator cannot delete their own account or a root account', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $root = User::factory()->create(['role' => UserRoleEnum::ROOT]);

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertUnprocessable();
    $this->actingAs($admin)->delete(route('admin.users.destroy', $root))->assertForbidden();

    expect($admin->fresh())->not->toBeNull()
        ->and($root->fresh())->not->toBeNull();
});
