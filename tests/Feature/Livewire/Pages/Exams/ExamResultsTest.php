<?php

use App\Livewire\Pages\Exams\ExamResults;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ExamResults::class)
        ->assertStatus(200);
});
