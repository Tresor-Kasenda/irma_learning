<?php

declare(strict_types=1);

it('dashboard loads under 200ms', function () {
    $start = microtime(true);

    $response = $this->get(route('dashboard'));

    $duration = (microtime(true) - $start) * 1000;

    expect($duration)
        ->toBeLessThan(200)
        ->and($response->getStatusCode())->toBe(302);
});

it('executes maximum 10 queries', function () {
    DB::enableQueryLog();

    $this->get(route('dashboard'));

    $queries = DB::getQueryLog();

    expect(count($queries))->toBeLessThanOrEqual(10);
});
