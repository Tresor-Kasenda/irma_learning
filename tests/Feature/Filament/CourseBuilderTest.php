<?php

declare(strict_types=1);

use App\Filament\Resources\FormationResource\Pages\EditFormation;
use App\Filament\Resources\FormationResource\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\SectionResource\Pages\EditSection;
use App\Filament\Resources\SectionResource\RelationManagers\ExamRelationManager;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

test('the sections relation manager lists a formation sections', function () {
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create(['title' => 'Introduction']);

    Livewire::test(SectionsRelationManager::class, [
        'ownerRecord' => $formation,
        'pageClass' => EditFormation::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$section]);
});

test('the section exam relation manager renders for a section', function () {
    $section = Section::factory()->for(Formation::factory())->create(['title' => 'Les bases']);

    Livewire::test(ExamRelationManager::class, [
        'ownerRecord' => $section,
        'pageClass' => EditSection::class,
    ])->assertSuccessful();
});
