<?php

declare(strict_types=1);

use App\Livewire\Pages\Certifications;
use App\Models\Formation;
use Livewire\Livewire;

it('renders active formations without price', function () {
    Formation::factory()->create([
        'is_active' => true,
        'price' => null,
    ]);

    Livewire::test(Certifications::class)
        ->assertStatus(200)
        ->assertSee('Gratuit');
});
