<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\ApiResponseMetadata;

it('normalizes header keys to lowercase', function () {
    $metadata = new ApiResponseMetadata(
        headers: ['Content-Type' => 'application/json', 'X-Request-ID' => 'req_123'],
        statusCode: 200,
    );

    expect($metadata->headers)->toHaveKey('content-type')
        ->and($metadata->headers)->toHaveKey('x-request-id')
        ->and($metadata->headers)->not->toHaveKey('Content-Type')
        ->and($metadata->headers)->not->toHaveKey('X-Request-ID');
});

it('stores the status code', function () {
    $metadata = new ApiResponseMetadata(headers: [], statusCode: 429);

    expect($metadata->statusCode)->toBe(429);
});

it('retrieves headers case-insensitively', function () {
    $metadata = new ApiResponseMetadata(
        headers: ['X-RateLimit-Remaining' => '99'],
        statusCode: 200,
    );

    expect($metadata->header('x-ratelimit-remaining'))->toBe('99')
        ->and($metadata->header('X-RateLimit-Remaining'))->toBe('99')
        ->and($metadata->header('X-RATELIMIT-REMAINING'))->toBe('99');
});

it('returns null for nonexistent headers', function () {
    $metadata = new ApiResponseMetadata(headers: [], statusCode: 200);

    expect($metadata->header('x-nonexistent'))->toBeNull();
});
