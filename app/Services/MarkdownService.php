<?php

declare(strict_types=1);

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownService
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = $this->createConverter();
    }

    /**
     * Convertir avec cache
     */
    public function toHtmlCached(string $markdown, ?string $cacheKey = null): string
    {
        if (empty($markdown)) {
            return '';
        }

        $key = $cacheKey ?? 'markdown.'.md5($markdown);

        return Cache::remember($key, 3600, function () use ($markdown) {
            return $this->toHtml($markdown);
        });
    }

    /**
     * Convertir Markdown en HTML
     *
     * @throws CommonMarkException
     */
    public function toHtml(string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        return $this->converter->convert($markdown)->getContent();
    }

    /**
     * Convertir et nettoyer le HTML
     *
     * @throws CommonMarkException
     */
    public function toSafeHtml(string $markdown): string
    {
        $html = $this->toHtml($markdown);

        return $this->sanitizeHtml($html);
    }

    /**
     * Créer un extrait
     *
     * @throws CommonMarkException
     */
    public function excerpt(string $markdown, int $length = 150): string
    {
        $text = $this->toText($markdown);

        return Str::limit($text, $length);
    }

    /**
     * Extraire le texte brut du Markdown
     *
     * @throws CommonMarkException
     */
    public function toText(string $markdown): string
    {
        $html = $this->toHtml($markdown);

        return strip_tags($html);
    }

    /**
     * Estimer le temps de lecture (mots par minute)
     *
     * @throws CommonMarkException
     */
    public function readingTime(string $markdown, int $wordsPerMinute = 200): int
    {
        $wordCount = $this->wordCount($markdown);

        return (int) ceil($wordCount / $wordsPerMinute);
    }

    /**
     * Compter les mots
     *
     * @throws CommonMarkException
     */
    public function wordCount(string $markdown): int
    {
        $text = $this->toText($markdown);

        return str_word_count($text);
    }

    /**
     * Générer une table des matières
     */
    public function tableOfContents(string $markdown): string
    {
        $headings = $this->extractHeadings($markdown);

        if (empty($headings)) {
            return '';
        }

        $toc = '<nav class="table-of-contents">'."\n";
        $toc .= '<ul>'."\n";

        foreach ($headings as $heading) {
            $indent = str_repeat('  ', $heading['level'] - 1);
            $toc .= $indent.'<li><a href="#'.$heading['slug'].'">'.htmlspecialchars($heading['text']).'</a></li>'."\n";
        }

        $toc .= '</ul>'."\n";
        $toc .= '</nav>';

        return $toc;
    }

    /**
     * Extraire les titres (headings)
     */
    public function extractHeadings(string $markdown): array
    {
        preg_match_all('/^(#{1,6})\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);

        $headings = [];
        foreach ($matches as $match) {
            $headings[] = [
                'level' => mb_strlen($match[1]),
                'text' => $match[2],
                'slug' => Str::slug($match[2]),
            ];
        }

        return $headings;
    }

    /**
     * Créer le convertisseur avec les extensions
     */
    private function createConverter(): MarkdownConverter
    {
        $config = config('markdown');

        // Créer l'environnement
        $environment = new Environment($config);

        // Ajouter les extensions selon la config
        if ($config['extensions']['table'] ?? false) {
            $environment->addExtension(new TableExtension());
        }

        if ($config['extensions']['strikethrough'] ?? false) {
            $environment->addExtension(new StrikethroughExtension());
        }

        if ($config['extensions']['autolink'] ?? false) {
            $environment->addExtension(new AutolinkExtension());
        }

        if ($config['extensions']['task_list'] ?? false) {
            $environment->addExtension(new TaskListExtension());
        }

        if ($config['extensions']['smart_punctuation'] ?? false) {
            $environment->addExtension(new SmartPunctExtension());
        }

        // Ajouter d'autres extensions utiles
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new FrontMatterExtension());

        return new MarkdownConverter($environment);
    }

    /**
     * Nettoyer le HTML de balises dangereuses
     */
    private function sanitizeHtml(string $html): string
    {
        // Utiliser HTMLPurifier pour nettoyer le HTML
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|title],ul,ol,li,br,span,code,pre,blockquote,h1,h2,h3,h4,h5,h6,img[src|alt],table,thead,tbody,tr,th,td');
        $config->set('AutoFormat.RemoveEmpty', true);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
