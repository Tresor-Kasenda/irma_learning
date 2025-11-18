<?php

declare(strict_types=1);

namespace App\Casts;

use App\Services\MarkdownService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use League\CommonMark\Exception\CommonMarkException;

final class Markdown implements CastsAttributes
{
    public function __construct(
        private bool $cached = false
    ) {}

    /**
     * @throws CommonMarkException
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        $markdown = app(MarkdownService::class);

        if ($this->cached) {
            return $markdown->toHtmlCached($value, get_class($model).'.'.$model->id.'.'.$key);
        }

        return $markdown->toHtml($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
