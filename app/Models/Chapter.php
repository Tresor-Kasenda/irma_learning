<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterTypeEnum;
use App\Services\MarkdownService;
use App\Services\MarkdownToHtmlConverter;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use League\CommonMark\Exception\CommonMarkException;

final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'description',
        'content',
        'video_url',
        'video_duration',
        'is_free',
        'is_active',
        'order_position',
        'metadata',
        'content_type',
        'excerpt',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function exams(): MorphOne
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress(): MorphMany
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    /**
     * Convertit le contenu Markdown en HTML avec styles
     *
     * @throws CommonMarkException
     */
    public function getHtmlContent(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $converter = app(MarkdownToHtmlConverter::class);

        return $converter->convertWithStyles($this->content);
    }

    /**
     * Convertit le contenu Markdown en HTML sans styles
     *
     * @throws CommonMarkException
     */
    public function getHtmlContentRaw(): string
    {
        if (empty($this->content)) {
            return '';
        }

        $converter = app(MarkdownToHtmlConverter::class);

        return $converter->convert($this->content);
    }

    /**
     * Obtenir le contenu en HTML
     *
     * @throws CommonMarkException
     */
    public function getContentHtmlAttribute(): string
    {
        return app(MarkdownService::class)->toHtml($this->content);
    }

    /**
     * Obtenir la description en HTML
     *
     * @throws CommonMarkException
     */
    public function getDescriptionHtmlAttribute(): string
    {
        if (empty($this->description)) {
            return '';
        }

        return app(MarkdownToHtmlConverter::class)->convert($this->description);
    }

    /**
     * Obtenir un extrait
     *
     * @throws CommonMarkException
     */
    public function getExcerptHtmlAttribute(): string
    {
        if ($this->excerpt) {
            return app(MarkdownService::class)->toHtml($this->excerpt);
        }

        return app(MarkdownService::class)->excerpt($this->content);
    }

    /**
     * Obtenir le temps de lecture
     *
     * @throws CommonMarkException
     */
    public function getReadingTimeAttribute(): int
    {
        return app(MarkdownService::class)->readingTime($this->content);
    }

    /**
     * Obtenir la table des matières
     */
    public function getTocAttribute(): string
    {
        return app(MarkdownService::class)->tableOfContents($this->content);
    }

    protected static function booted(): void
    {
        self::creating(function (Chapter $chapter) {
            if (empty($chapter->order_position)) {
                $maxPosition = static::where('section_id', $chapter->section_id)
                    ->max('order_position') ?? 0;
                $chapter->order_position = $maxPosition + 1;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
            'content_type' => ChapterTypeEnum::class,
        ];
    }
}
