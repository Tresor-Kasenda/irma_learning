<?php

use App\Livewire\Pages\Admins\DashboardAdmin;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(DashboardAdmin::class)
        ->assertStatus(200);
});
