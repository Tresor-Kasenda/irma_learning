<?php

declare(strict_types=1);

use App\Models\Formation;
use App\Services\FormationAssessmentReadinessService;
use Database\Seeders\ProfessionalDemoContentSeeder;
use Illuminate\Support\Facades\Storage;

test('professional demo content contains real media and complete assessments', function () {
    Storage::fake('public');

    $this->seed(ProfessionalDemoContentSeeder::class);

    $formation = Formation::query()
        ->where('title', 'Pilotage des risques opérationnels — Parcours professionnel')
        ->with(['sections.chapters', 'sections.exam.questions.options', 'exam.questions.options'])
        ->firstOrFail();

    expect($formation->is_active)->toBeTrue()
        ->and($formation->is_certifying)->toBeTrue()
        ->and($formation->sections)->toHaveCount(3)
        ->and($formation->sections->flatMap->chapters->pluck('content_type')->map->value->all())
        ->toContain('text', 'video', 'pdf')
        ->and(app(FormationAssessmentReadinessService::class)->issues($formation))->toBeEmpty();

    Storage::disk('public')->assertExists($formation->image);
    Storage::disk('public')->assertExists('demo/welcome-to-the-course.mp4');
    Storage::disk('public')->assertExists('demo/risk-register-workbook.pdf');
});
