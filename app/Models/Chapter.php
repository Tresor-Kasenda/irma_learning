<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChapterTypeEnum;
use App\Models\Concerns\LogsAllActivity;
use App\Services\CatalogStatsService;
use App\Services\MarkdownService;
use App\Services\MarkdownToHtmlConverter;
use Database\Factories\ChapterFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Exception\CommonMarkException;

final class Chapter extends Model
{
    /** @use HasFactory<ChapterFactory> */
    use HasFactory;

    use LogsAllActivity;

    protected $fillable = [
        'section_id',
        'title',
        'description',
        'content',
        'content_type',
        'media_url',
        'video_url',
        'cover_image',
        'markdown_file',
        'processing_status',
        'processing_error',
        'processing_metadata',
        'processing_started_at',
        'processed_at',
        'duration_minutes',
        'order_position',
        'is_free',
        'is_active',
    ];

    /**
     * Accesseurs calculés toujours ajoutés à la sérialisation du modèle.
     *
     * @var list<string>
     */
    protected $appends = [
        'video_href',
        'media_href',
        'cover_image_href',
        'markdown_file_href',
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

        self::saved(fn (): null => self::flushCatalogStats());
        self::deleted(fn (): null => self::flushCatalogStats());
    }

    /**
     * URL publique résolue de la vidéo (compatible disque local ou S3).
     */
    protected function videoHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveMediaUrl($this->video_url));
    }

    /**
     * URL publique résolue du média PDF (compatible disque local ou S3).
     */
    protected function mediaHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveMediaUrl($this->media_url));
    }

    /**
     * URL publique résolue de l'image de couverture (compatible disque local ou S3).
     */
    protected function coverImageHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveMediaUrl($this->cover_image));
    }

    /**
     * URL publique résolue du fichier Markdown extrait (compatible disque local ou S3).
     */
    protected function markdownFileHref(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->resolveMediaUrl($this->markdown_file));
    }

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'processing_metadata' => 'array',
            'processing_started_at' => 'datetime',
            'processed_at' => 'datetime',
            'content_type' => ChapterTypeEnum::class,
        ];
    }

    private static function flushCatalogStats(): null
    {
        app(CatalogStatsService::class)->forget();

        return null;
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        return $path === null || $path === '' ? null : Storage::disk('public')->url($path);
    }
}
