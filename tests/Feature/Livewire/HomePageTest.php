<?php

declare(strict_types=1);

use App\Livewire\Pages\HomePage;
use App\Models\Formation;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(HomePage::class)
        ->assertStatus(200);
});

it('renders an active formation without price', function () {
    Formation::factory()->create([
        'is_active' => true,
        'price' => null,
    ]);

    Livewire::test(HomePage::class)
        ->assertStatus(200)
        ->assertSee('Gratuit');
});
