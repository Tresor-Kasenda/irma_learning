<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ChapterTypeEnum;
use App\Models\Chapter;
use App\Models\Formation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class CatalogStatsService
{
    public const CACHE_KEY = 'catalog_stats';

    private const TTL_SECONDS = 3600;

    /**
     * Active catalog counts shared across the dashboard and the public catalog.
     *
     * @return array{formations: int, videos: int, pdfs: int, texts: int}
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function (): array {
            $counts = Chapter::query()
                ->where('chapters.is_active', true)
                ->whereHas('section.formation', fn (Builder $query): Builder => $query->active())
                ->selectRaw(
                    'count(case when content_type = ? then 1 end) as videos,'
                    .' count(case when content_type = ? then 1 end) as pdfs,'
                    .' count(case when content_type = ? then 1 end) as texts',
                    [
                        ChapterTypeEnum::VIDEO->value,
                        ChapterTypeEnum::PDF->value,
                        ChapterTypeEnum::TEXT->value,
                    ],
                )
                ->first();

            return [
                'formations' => Formation::query()->active()->count(),
                'videos' => (int) ($counts->videos ?? 0),
                'pdfs' => (int) ($counts->pdfs ?? 0),
                'texts' => (int) ($counts->texts ?? 0),
            ];
        });
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
