<?php

declare(strict_types=1);

use App\Models\Chapter;

afterEach(function () {
    app('filesystem')->forgetDisk('public');
});

test('chapter media href resolve through the local public disk by default', function () {
    $chapter = Chapter::factory()->make([
        'video_url' => 'chapters/video.mp4',
        'media_url' => null,
        'cover_image' => null,
        'markdown_file' => null,
    ]);

    expect($chapter->video_href)->toBe(config('app.url').'/storage/chapters/video.mp4')
        ->and($chapter->media_href)->toBeNull()
        ->and($chapter->cover_image_href)->toBeNull()
        ->and($chapter->markdown_file_href)->toBeNull();
});

test('chapter media href resolve through S3 once the public disk is switched over', function () {
    config()->set('filesystems.disks.public', [
        'driver' => 's3',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'region' => 'eu-west-1',
        'bucket' => 'irma-learning',
        'url' => 'https://cdn.example.com',
        'endpoint' => null,
        'use_path_style_endpoint' => false,
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
    ]);
    app('filesystem')->forgetDisk('public');

    $chapter = Chapter::factory()->make([
        'video_url' => 'chapters/video.mp4',
        'cover_image' => 'chapters/extracted/1/cover.jpg',
    ]);

    expect($chapter->video_href)->toBe('https://cdn.example.com/chapters/video.mp4')
        ->and($chapter->cover_image_href)->toBe('https://cdn.example.com/chapters/extracted/1/cover.jpg');
});
