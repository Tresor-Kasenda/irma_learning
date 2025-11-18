<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration CommonMark
    |--------------------------------------------------------------------------
    */
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

    'html_input' => 'strip',
    'allow_unsafe_links' => false,
    'max_nesting_level' => PHP_INT_MAX,

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    */
    'extensions' => [
        // Ajouter les extensions nécessaires
        'table' => true,
        'strikethrough' => true,
        'autolink' => true,
        'task_list' => true,
        'smart_punctuation' => true,
    ],
];
