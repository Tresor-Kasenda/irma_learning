<?php

declare(strict_types=1);

use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('guests are redirected to login', function () {
    $this->get(route('admin.activity-logs.index'))->assertRedirect(route('login'));
});

test('a student cannot access the activity log', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('admin.activity-logs.index'))
        ->assertForbidden();
});

test('creating a formation while authenticated logs an activity with the causer', function () {
    $this->actingAs($this->admin);

    $formation = Formation::factory()->create(['title' => 'Laravel avancé']);

    expect($formation->activities()->count())->toBe(1);

    $activity = $formation->activities()->first();

    expect($activity->event)->toBe('created')
        ->and($activity->causer_id)->toBe($this->admin->id)
        ->and($activity->properties->get('ip_address'))->not->toBeNull();
});

test('updating a formation logs only the dirty attributes', function () {
    $this->actingAs($this->admin);

    $formation = Formation::factory()->create(['title' => 'Laravel']);
    $formation->update(['title' => 'Laravel avancé']);

    $activity = $formation->activities()->latest('id')->first();

    expect($activity->event)->toBe('updated')
        ->and($activity->properties->get('attributes'))->toHaveKey('title')
        ->and($activity->properties->get('old'))->toHaveKey('title');
});

test('the index lists activities for the admin', function () {
    $this->actingAs($this->admin);

    Formation::factory()->create();

    $this->get(route('admin.activity-logs.index', ['log_name' => 'formation']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ActivityLogs/Index')
            ->has('activities.data', 1)
            ->etc());
});

test('the index can be filtered by log name', function () {
    $this->actingAs($this->admin);

    Formation::factory()->create();
    User::factory()->create();

    $this->get(route('admin.activity-logs.index', ['log_name' => 'formation']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('activities.data', 1)
            ->where('activities.data.0.log_name', 'formation')
            ->etc());
});
