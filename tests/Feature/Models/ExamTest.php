<?php

declare(strict_types=1);

use App\Models\Exam;
use App\Models\Question;

test('exam total points is always returned as an integer', function () {
    $exam = Exam::factory()->create();

    Question::factory()->for($exam)->create(['points' => 4]);
    Question::factory()->for($exam)->create(['points' => 6]);

    expect($exam->getTotalPoints())->toBe(10);
});

test('an exam without questions has zero total points', function () {
    expect(Exam::factory()->create()->getTotalPoints())->toBe(0);
});
