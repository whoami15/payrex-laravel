<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Exceptions\AuthenticationException;
use LegionHQ\LaravelPayrex\Exceptions\InvalidRequestException;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Exceptions\ResourceNotFoundException;
use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;

it('creates a PayrexApiException from a response with errors', function () {
    $body = loadFixture('errors/invalid_request.json');

    $exception = PayrexApiException::fromResponse($body, 400);

    expect($exception)
        ->toBeInstanceOf(PayrexApiException::class)
        ->getMessage()->toBe('The amount field is required.')
        ->and($exception->errors)->toBe($body['errors'])
        ->and($exception->statusCode)->toBe(400)
        ->and($exception->body)->toBe($body);
});

it('creates a PayrexApiException with default message when errors array is empty', function () {
    $body = ['errors' => []];

    $exception = PayrexApiException::fromResponse($body, 500);

    expect($exception->getMessage())->toBe('An API error occurred.')
        ->and($exception->errors)->toBe([])
        ->and($exception->statusCode)->toBe(500);
});

it('creates a PayrexApiException with default message when errors key is missing', function () {
    $body = ['message' => 'Something went wrong'];

    $exception = PayrexApiException::fromResponse($body, 500);

    expect($exception->getMessage())->toBe('An API error occurred.')
        ->and($exception->errors)->toBe([]);
});

it('creates an AuthenticationException from a response', function () {
    $body = loadFixture('errors/authentication.json');

    $exception = AuthenticationException::fromResponse($body, 401);

    expect($exception)
        ->toBeInstanceOf(AuthenticationException::class)
        ->toBeInstanceOf(PayrexApiException::class)
        ->and($exception->statusCode)->toBe(401)
        ->and($exception->getMessage())->toBe('Invalid API key provided.');
});

it('creates an InvalidRequestException from a response', function () {
    $body = loadFixture('errors/invalid_request.json');

    $exception = InvalidRequestException::fromResponse($body, 400);

    expect($exception)
        ->toBeInstanceOf(InvalidRequestException::class)
        ->toBeInstanceOf(PayrexApiException::class)
        ->and($exception->statusCode)->toBe(400)
        ->and($exception->getMessage())->toBe('The amount field is required.');
});

it('creates a ResourceNotFoundException from a response', function () {
    $body = loadFixture('errors/resource_not_found.json');

    $exception = ResourceNotFoundException::fromResponse($body, 404);

    expect($exception)
        ->toBeInstanceOf(ResourceNotFoundException::class)
        ->toBeInstanceOf(PayrexApiException::class)
        ->and($exception->statusCode)->toBe(404)
        ->and($exception->getMessage())->toBe('The resource with ID pi_nonexistent was not found.');
});

it('WebhookVerificationException is a plain Exception, not a PayrexApiException', function () {
    $exception = new WebhookVerificationException('Invalid signature');

    expect($exception)
        ->toBeInstanceOf(Exception::class)
        ->not->toBeInstanceOf(PayrexApiException::class)
        ->and($exception->getMessage())->toBe('Invalid signature');
});

it('WebhookVerificationException has named constructors for each error type', function () {
    expect(WebhookVerificationException::missingHeader())
        ->toBeInstanceOf(WebhookVerificationException::class)
        ->getMessage()->toBe('Missing Payrex-Signature header.');
});
