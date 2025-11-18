<?php

declare(strict_types=1);

if (! function_exists('markdown')) {
    /**
     * Convertir Markdown en HTML
     */
    function markdown(string $content): string
    {
        return app('markdown')->toHtml($content);
    }
}

if (! function_exists('markdown_safe')) {
    /**
     * Convertir Markdown en HTML sécurisé
     */
    function markdown_safe(string $content): string
    {
        return app('markdown')->toSafeHtml($content);
    }
}
