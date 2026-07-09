<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
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

test('a student cannot access user administration', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
