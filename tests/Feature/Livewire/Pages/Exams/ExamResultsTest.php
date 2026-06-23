<?php

declare(strict_types=1);

use App\Enums\ExamAttemptEnum;
use App\Livewire\Pages\Exams\ExamResults;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $attempt = ExamAttempt::factory()
        ->for($exam)
        ->for($user)
        ->create([
            'status' => ExamAttemptEnum::COMPLETED,
            'completed_at' => now(),
            'score' => 8,
            'max_score' => 10,
            'percentage' => 80,
        ]);

    Livewire::actingAs($user)
        ->test(ExamResults::class, ['attempt' => $attempt])
        ->assertStatus(200);
});
