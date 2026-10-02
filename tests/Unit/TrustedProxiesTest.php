<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Route::get('/__proxy-scheme', fn () => response()->json([
        'url' => url('/'),
        'secure' => request()->isSecure(),
    ]));
});

it('generates https urls when the proxy forwards the https scheme', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
        ->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'formations.btpcma.org',
        ])
        ->getJson('http://localhost/__proxy-scheme')
        ->assertSuccessful()
        ->assertJson([
            'url' => 'https://formations.btpcma.org',
            'secure' => true,
        ]);
});

it('keeps http urls when no proxy header is sent', function () {
    $this->getJson('http://localhost/__proxy-scheme')
        ->assertSuccessful()
        ->assertJson(['secure' => false]);
});
