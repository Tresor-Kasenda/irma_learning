<?php

declare(strict_types=1);

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

    // Create enrollments with different statuses
    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[0]->id,
        'status' => 'active',
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[1]->id,
        'status' => 'completed',
    ]);

    Enrollment::factory()->create([
        'user_id' => $user->id,
        'formation_id' => $formations[2]->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Total formations', false);
    $response->assertSee('En cours', false);
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
        'status' => 'active',
        'progress_percentage' => 50,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Test Formation', false);
    $response->assertSee('Mes formations en cours', false);
});

test('dashboard shows available formations', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create([
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
        'status' => 'active',
    ]);

    $component = Livewire::actingAs($user)->test(App\Livewire\Pages\Dashboard::class);

    $component->assertStatus(200);
    $component->assertViewHas('totalEnrollments', 1);
    $component->assertViewHas('activeEnrollments', 1);
    $component->assertViewHas('availableFormations');
});
