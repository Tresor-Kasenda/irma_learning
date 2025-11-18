<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string toHtml(string $markdown)
 * @method static string toHtmlCached(string $markdown, ?string $cacheKey = null)
 * @method static string toSafeHtml(string $markdown)
 * @method static string toText(string $markdown)
 * @method static string excerpt(string $markdown, int $length = 150)
 * @method static int wordCount(string $markdown)
 * @method static int readingTime(string $markdown, int $wordsPerMinute = 200)
 * @method static array extractHeadings(string $markdown)
 * @method static string tableOfContents(string $markdown)
 */
final class Markdown extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'markdown';
    }
}
