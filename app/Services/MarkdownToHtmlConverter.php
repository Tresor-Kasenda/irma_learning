<?php

declare(strict_types=1);

namespace App\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Service de conversion Markdown vers HTML
 *
 * Utilise League CommonMark avec les extensions suivantes:
 * - GitHub Flavored Markdown (tables, strikethrough, task lists)
 * - Attributes (pour ajouter des classes CSS personnalisées)
 * - Support complet des tableaux
 */
final class MarkdownToHtmlConverter
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = $this->createConverter();
    }

    /**
     * Convertit du Markdown en HTML avec des styles personnalisés
     *
     * @throws CommonMarkException
     */
    public function convertWithStyles(string $markdown): string
    {
        $html = $this->convert($markdown);

        return $this->wrapWithStyles($html);
    }

    /**
     * Convertit du Markdown en HTML
     *
     * @throws CommonMarkException
     */
    public function convert(string $markdown): string
    {
        $html = $this->converter->convert($markdown);

        return $html->getContent();
    }

    /**
     * Crée le convertisseur avec toutes les extensions nécessaires
     */
    private function createConverter(): MarkdownConverter
    {
        $config = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
            'renderer' => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
                'soft_break' => "\n",
            ],
            'commonmark' => [
                'enable_em' => true,
                'enable_strong' => true,
                'use_asterisk' => true,
                'use_underscore' => true,
                'unordered_list_markers' => ['-', '*', '+'],
            ],
            'table' => [
                'wrap' => [
                    'enabled' => true,
                    'tag' => 'div',
                    'attributes' => ['class' => 'table-responsive'],
                ],
            ],
        ];

        $environment = new Environment($config);

        // Ajouter les extensions
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new AttributesExtension);

        return new MarkdownConverter($environment);
    }

    /**
     * Enveloppe le HTML avec des styles CSS pour un meilleur affichage
     */
    private function wrapWithStyles(string $html): string
    {
        $styles = '
        <style>
            .markdown-content {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.8;
                color: #1f2937;
                max-width: 100%;
            }

            /* Titres */
            .markdown-content h1 {
                font-size: 2.25rem;
                font-weight: 700;
                margin-top: 2rem;
                margin-bottom: 1rem;
                color: #111827;
                border-bottom: 2px solid #e5e7eb;
                padding-bottom: 0.5rem;
            }

            .markdown-content h2 {
                font-size: 1.875rem;
                font-weight: 600;
                margin-top: 1.75rem;
                margin-bottom: 0.875rem;
                color: #1f2937;
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 0.375rem;
            }

            .markdown-content h3 {
                font-size: 1.5rem;
                font-weight: 600;
                margin-top: 1.5rem;
                margin-bottom: 0.75rem;
                color: #374151;
            }

            .markdown-content h4 {
                font-size: 1.25rem;
                font-weight: 600;
                margin-top: 1.25rem;
                margin-bottom: 0.625rem;
                color: #4b5563;
            }

            .markdown-content h5 {
                font-size: 1.125rem;
                font-weight: 600;
                margin-top: 1rem;
                margin-bottom: 0.5rem;
                color: #6b7280;
            }

            .markdown-content h6 {
                font-size: 1rem;
                font-weight: 600;
                margin-top: 0.875rem;
                margin-bottom: 0.5rem;
                color: #9ca3af;
            }

            /* Paragraphes */
            .markdown-content p {
                margin-bottom: 1rem;
                line-height: 1.8;
            }

            /* Listes */
            .markdown-content ul,
            .markdown-content ol {
                margin-bottom: 1rem;
                padding-left: 2rem;
            }

            .markdown-content ul {
                list-style-type: disc;
            }

            .markdown-content ol {
                list-style-type: decimal;
            }

            .markdown-content li {
                margin-bottom: 0.5rem;
                line-height: 1.6;
            }

            .markdown-content li > p {
                margin-bottom: 0.5rem;
            }

            .markdown-content ul ul,
            .markdown-content ol ul {
                list-style-type: circle;
                margin-top: 0.5rem;
            }

            .markdown-content ul ul ul,
            .markdown-content ol ul ul {
                list-style-type: square;
            }

            /* Liens */
            .markdown-content a {
                color: #3b82f6;
                text-decoration: none;
                border-bottom: 1px solid transparent;
                transition: all 0.2s ease;
            }

            .markdown-content a:hover {
                color: #2563eb;
                border-bottom-color: #2563eb;
            }

            /* Code inline */
            .markdown-content code {
                background-color: #f3f4f6;
                padding: 0.125rem 0.375rem;
                border-radius: 0.25rem;
                font-family: "Monaco", "Courier New", monospace;
                font-size: 0.875rem;
                color: #dc2626;
            }

            /* Blocs de code */
            .markdown-content pre {
                background-color: #1f2937;
                color: #f9fafb;
                padding: 1rem;
                border-radius: 0.5rem;
                overflow-x: auto;
                margin-bottom: 1rem;
                font-family: "Monaco", "Courier New", monospace;
                font-size: 0.875rem;
                line-height: 1.5;
            }

            .markdown-content pre code {
                background-color: transparent;
                padding: 0;
                color: inherit;
                font-size: inherit;
            }

            /* Citations */
            .markdown-content blockquote {
                border-left: 4px solid #3b82f6;
                margin: 1rem 0;
                color: #6b7280;
                font-style: italic;
                background-color: #f9fafb;
                padding: 1rem;
                border-radius: 0.25rem;
            }

            .markdown-content blockquote p {
                margin-bottom: 0.5rem;
            }

            .markdown-content blockquote p:last-child {
                margin-bottom: 0;
            }

            /* Tableaux */
            .markdown-content .table-responsive {
                overflow-x: auto;
                margin-bottom: 1rem;
            }

            .markdown-content table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1rem;
                background-color: #ffffff;
            }

            .markdown-content thead {
                background-color: #f3f4f6;
            }

            .markdown-content th {
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                color: #111827;
                border-bottom: 2px solid #e5e7eb;
            }

            .markdown-content td {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .markdown-content tbody tr:hover {
                background-color: #f9fafb;
            }

            /* Séparateurs horizontaux */
            .markdown-content hr {
                border: none;
                border-top: 2px solid #e5e7eb;
                margin: 2rem 0;
            }

            /* Images */
            .markdown-content img {
                max-width: 100%;
                height: auto;
                border-radius: 0.5rem;
                margin: 1rem 0;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            /* Emphase et strong */
            .markdown-content em {
                font-style: italic;
            }

            .markdown-content strong {
                font-weight: 600;
                color: #111827;
            }

            /* Task lists (GitHub style) */
            .markdown-content input[type="checkbox"] {
                margin-right: 0.5rem;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .markdown-content h1 {
                    font-size: 1.875rem;
                }

                .markdown-content h2 {
                    font-size: 1.5rem;
                }

                .markdown-content h3 {
                    font-size: 1.25rem;
                }

                .markdown-content pre {
                    padding: 0.75rem;
                    font-size: 0.8125rem;
                }
            }
        </style>
        ';

        return $styles.'<div class="markdown-content">'.$html.'</div>';
    }
}
