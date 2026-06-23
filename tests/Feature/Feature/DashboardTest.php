<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Livewire\Pages\Students\DashboardStudent;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Livewire\Livewire;

test('dashboard loads successfully for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Bonjour', false);
});

test('dashboard displays user statistics', function () {
    $user = User::factory()->create();
    $formations = Formation::factory()->count(3)->create(['is_active' => true]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[0]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[1]->id,
        'status' => EnrollmentStatusEnum::COMPLETED,
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[2]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Formations', false);
    $response->assertSee('Certificats', false);
});

test('dashboard shows enrolled formations', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create([
        'is_active' => true,
        'title' => 'Test Formation',
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formation->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 50,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Test Formation', false);
    $response->assertSee('Mes formations', false);
});

test('dashboard shows available formations', function () {
    $user = User::factory()->create();
    Formation::factory()->create([
        'is_active' => true,
        'title' => 'Available Formation',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Available Formation', false);
});

test('dashboard component renders correctly with livewire', function () {
    $user = User::factory()->create();
    $formations = Formation::factory()->count(2)->create(['is_active' => true]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[0]->id,
        'status' => EnrollmentStatusEnum::ACTIVE,
    ]);

    Livewire::actingAs($user)
        ->test(DashboardStudent::class)
        ->assertStatus(200);
});
