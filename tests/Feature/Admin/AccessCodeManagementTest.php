<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Models\Formation;
use App\Models\FormationAccessCode;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an admin can list access codes', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    FormationAccessCode::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.access-codes.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/AccessCodes/Index')
            ->has('codes.data', 3));
});

test('an admin can generate a batch of access codes', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.access-codes.generate'), [
            'formation_id' => $formation->id,
            'quantity' => 10,
        ])
        ->assertRedirect();

    expect(FormationAccessCode::query()->where('formation_id', $formation->id)->count())->toBe(10)
        ->and(FormationAccessCode::query()->pluck('code')->unique())->toHaveCount(10);
});

test('an admin cannot delete an already used access code', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $code = FormationAccessCode::factory()->used()->create();

    $this->actingAs($admin)
        ->delete(route('admin.access-codes.destroy', $code))
        ->assertStatus(422);

    expect(FormationAccessCode::query()->find($code->id))->not->toBeNull();
});

test('an admin can delete an unused access code', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $code = FormationAccessCode::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.access-codes.destroy', $code))
        ->assertRedirect();

    expect(FormationAccessCode::query()->find($code->id))->toBeNull();
});

test('an admin can export access codes as csv', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    FormationAccessCode::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.access-codes.export'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('a student cannot access the access codes admin', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.access-codes.index'))
        ->assertForbidden();
});
