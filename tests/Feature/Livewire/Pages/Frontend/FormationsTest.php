<?php

use App\Livewire\Pages\Frontend\Formations;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Formations::class)
        ->assertStatus(200);
});
