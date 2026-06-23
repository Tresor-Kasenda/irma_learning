<?php

declare(strict_types=1);

use App\Livewire\Pages\HomePage;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(HomePage::class)
        ->assertStatus(200);
});
