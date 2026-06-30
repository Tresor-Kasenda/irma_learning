<?php

declare(strict_types=1);

use App\Enums\ChapterTypeEnum;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Services\CatalogStatsService;
use Illuminate\Support\Facades\Cache;

function makeChapter(Formation $formation, string $type, bool $active = true): Chapter
{
    $section = Section::factory()->for($formation)->create();

    return Chapter::factory()->for($section)->create([
        'content_type' => $type,
        'is_active' => $active,
    ]);
}

test('it computes active catalog counts', function () {
    $formation = Formation::factory()->create(['is_active' => true]);
    Formation::factory()->create(['is_active' => true]);
    Formation::factory()->create(['is_active' => false]);

    makeChapter($formation, ChapterTypeEnum::VIDEO->value);
    makeChapter($formation, ChapterTypeEnum::VIDEO->value);
    makeChapter($formation, ChapterTypeEnum::PDF->value);
    makeChapter($formation, ChapterTypeEnum::TEXT->value);
    makeChapter($formation, ChapterTypeEnum::VIDEO->value, active: false);

    expect(app(CatalogStatsService::class)->get())->toBe([
        'formations' => 2,
        'videos' => 2,
        'pdfs' => 1,
        'texts' => 1,
    ]);
});

test('it serves a cached result until forgotten', function () {
    Formation::factory()->create(['is_active' => true]);

    $service = app(CatalogStatsService::class);

    // A pre-seeded cache entry must be returned as-is, proving get() does not recompute.
    Cache::put(CatalogStatsService::CACHE_KEY, [
        'formations' => 99,
        'videos' => 5,
        'pdfs' => 3,
        'texts' => 1,
    ], 60);

    expect($service->get())->toBe([
        'formations' => 99,
        'videos' => 5,
        'pdfs' => 3,
        'texts' => 1,
    ]);

    $service->forget();

    expect($service->get()['formations'])->toBe(1);
});

test('saving a formation invalidates the cache', function () {
    Formation::factory()->create(['is_active' => true]);
    $service = app(CatalogStatsService::class);

    expect($service->get()['formations'])->toBe(1);

    Formation::factory()->create(['is_active' => true]);

    expect($service->get()['formations'])->toBe(2);
});

test('saving a chapter invalidates the cache', function () {
    $formation = Formation::factory()->create(['is_active' => true]);
    $service = app(CatalogStatsService::class);

    expect($service->get()['videos'])->toBe(0);

    makeChapter($formation, ChapterTypeEnum::VIDEO->value);

    expect($service->get()['videos'])->toBe(1);
});
