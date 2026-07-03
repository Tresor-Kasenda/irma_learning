<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'subscriptions_ttl' => env('SUBSCRIPTIONS_CACHE_TTL', 3600), // 1 hour
        'master_classes_ttl' => env('MASTER_CLASSES_CACHE_TTL', 1800), // 30 minutes
        'user_progress_ttl' => env('USER_PROGRESS_CACHE_TTL', 600), // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    */
    'database' => [
        'enable_query_caching' => env('DB_QUERY_CACHE', true),
        'eager_load_relations' => [
            'master_class' => ['chapters', 'resources', 'subscriptions'],
            'subscription' => ['user', 'masterClass', 'chapterProgress'],
            'chapter' => ['progress', 'examination'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    */
    'subscription' => [
        'auto_enroll_free_courses' => env('AUTO_ENROLL_FREE_COURSES', true),
        'max_active_subscriptions' => env('MAX_ACTIVE_SUBSCRIPTIONS', 10),
        'progress_update_threshold' => env('PROGRESS_UPDATE_THRESHOLD', 5), // Update every 5%
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'send_subscription_welcome' => env('SEND_SUBSCRIPTION_WELCOME', true),
        'send_progress_updates' => env('SEND_PROGRESS_UPDATES', true),
        'send_completion_certificates' => env('SEND_COMPLETION_CERTIFICATES', true),
    ],

    'pdf_extraction' => [
        'python_binary' => env('PDF_PYTHON_BINARY', base_path('.venv-pdf/bin/python')),
        'script_path' => resource_path('python/pdf_to_markdown.py'),
        'timeout' => (int) env('PDF_EXTRACTION_TIMEOUT', 600),
        'max_pages' => (int) env('PDF_EXTRACTION_MAX_PAGES', 0),
        'image_dpi' => (int) env('PDF_EXTRACTION_IMAGE_DPI', 144),
        'visual_dpi' => (int) env('PDF_EXTRACTION_VISUAL_DPI', 110),
        'ocr_language' => env('PDF_EXTRACTION_OCR_LANGUAGE', 'fra+eng'),
        'batch_size' => (int) env('PDF_EXTRACTION_BATCH_SIZE', 50),
        'parallel' => (int) env('PDF_EXTRACTION_PARALLEL', 0),
    ],
];
