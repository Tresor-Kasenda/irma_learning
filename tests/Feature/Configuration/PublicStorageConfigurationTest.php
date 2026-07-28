<?php

declare(strict_types=1);

/**
 * @param  array<string, string>  $environment
 * @return array<string, mixed>
 */
function publicStorageDiskConfiguration(array $environment): array
{
    $keys = [
        'FILESYSTEM_PUBLIC_DRIVER',
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
        'AWS_DEFAULT_REGION',
        'AWS_BUCKET',
        'AWS_URL',
        'AWS_ENDPOINT',
        'AWS_USE_PATH_STYLE_ENDPOINT',
    ];

    $previousValues = [];
    $previousEnvironment = [];
    $previousServer = [];
    foreach ($keys as $key) {
        $previousValues[$key] = getenv($key);
        $previousEnvironment[$key] = array_key_exists($key, $_ENV) ? $_ENV[$key] : null;
        $previousServer[$key] = array_key_exists($key, $_SERVER) ? $_SERVER[$key] : null;
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }

    foreach ($environment as $key => $value) {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key.'='.$value);
    }

    try {
        /** @var array{disks: array{public: array<string, mixed>}} $filesystems */
        $filesystems = require config_path('filesystems.php');

        return $filesystems['disks']['public'];
    } finally {
        foreach ($previousValues as $key => $value) {
            if ($previousEnvironment[$key] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $previousEnvironment[$key];
            }

            if ($previousServer[$key] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $previousServer[$key];
            }

            $value === false ? putenv($key) : putenv($key.'='.$value);
        }
    }
}

test('public storage falls back to the local disk when S3 credentials are incomplete', function () {
    $disk = publicStorageDiskConfiguration([
        'FILESYSTEM_PUBLIC_DRIVER' => 's3',
        'AWS_ACCESS_KEY_ID' => 'test-key',
        'AWS_SECRET_ACCESS_KEY' => '',
        'AWS_DEFAULT_REGION' => 'eu-west-1',
        'AWS_BUCKET' => 'irma-learning',
    ]);

    expect($disk['driver'])->toBe('local')
        ->and($disk['root'])->toBe(storage_path('app/public'));
});

test('public storage uses S3 only when all required settings are present', function () {
    $disk = publicStorageDiskConfiguration([
        'FILESYSTEM_PUBLIC_DRIVER' => 's3',
        'AWS_ACCESS_KEY_ID' => 'test-key',
        'AWS_SECRET_ACCESS_KEY' => 'test-secret',
        'AWS_DEFAULT_REGION' => 'eu-west-1',
        'AWS_BUCKET' => 'irma-learning',
        'AWS_URL' => 'https://cdn.example.com',
    ]);

    expect($disk['driver'])->toBe('s3')
        ->and($disk['bucket'])->toBe('irma-learning')
        ->and($disk['url'])->toBe('https://cdn.example.com');
});
