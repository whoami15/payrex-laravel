<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\WebhookReceived;
use LegionHQ\LaravelPayrex\Exceptions\AuthenticationException;
use LegionHQ\LaravelPayrex\Exceptions\InvalidRequestException;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Exceptions\RateLimitException;
use LegionHQ\LaravelPayrex\Exceptions\ResourceNotFoundException;
use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;
use LegionHQ\LaravelPayrex\PayrexClient;

it('constructs a typed event from a valid webhook payload', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode([
        'id' => 'evt_123',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['id' => 'pi_123', 'resource' => 'payment_intent', 'amount' => 50000],
    ]);

    $header = buildWebhookSignature($payload, 'whsec_test');
    $event = $client->constructEvent($payload, $header, 'whsec_test');

    /** @var PaymentIntent $data */
    $data = $event->data();

    expect($event)
        ->toBeInstanceOf(PaymentIntentSucceeded::class)
        ->and($event->eventType())->toBe(WebhookEventType::PaymentIntentSucceeded)
        ->and($data)->toBeInstanceOf(PaymentIntent::class)
        ->and($data->id)->toBe('pi_123')
        ->and($data['amount'])->toBe(50000)
        ->and($event->isLiveMode())->toBeFalse();
});

it('falls back to WebhookReceived for unknown event types', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode([
        'id' => 'evt_456',
        'type' => 'unknown.event',
        'livemode' => false,
        'data' => ['id' => 'res_456', 'resource' => 'test'],
    ]);

    $header = buildWebhookSignature($payload, 'whsec_test');
    $event = $client->constructEvent($payload, $header, 'whsec_test');

    expect($event)
        ->toBeInstanceOf(WebhookReceived::class)
        ->and($event->eventType())->toBeNull();
});

it('throws on invalid signature in constructEvent', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode(['type' => 'payment_intent.succeeded']);
    $timestamp = time();
    $header = "t={$timestamp},te=invalid,li=";

    $client->constructEvent($payload, $header, 'whsec_test');
})->throws(WebhookVerificationException::class);

it('throws on invalid JSON payload in constructEvent', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = 'not-json';
    $header = buildWebhookSignature($payload, 'whsec_test');

    $client->constructEvent($payload, $header, 'whsec_test');
})->throws(WebhookVerificationException::class, 'Invalid JSON payload.');

it('respects custom tolerance in constructEvent', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode(['type' => 'payment_intent.succeeded', 'data' => ['id' => 'pi_123', 'resource' => 'payment_intent']]);
    $oldTimestamp = time() - 600;
    $header = buildWebhookSignature($payload, 'whsec_test', $oldTimestamp);

    $client->constructEvent($payload, $header, 'whsec_test', tolerance: 0);
})->throwsNoExceptions();

it('uses configured webhook secret when secret is null', function () {
    $client = new PayrexClient(
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.payrexhq.com',
        webhookSecret: 'whsec_configured',
    );

    $payload = json_encode([
        'id' => 'evt_123',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['id' => 'pi_123', 'resource' => 'payment_intent', 'amount' => 50000],
    ]);

    $header = buildWebhookSignature($payload, 'whsec_configured');
    $event = $client->constructEvent($payload, $header);

    expect($event)->toBeInstanceOf(PaymentIntentSucceeded::class);
});

it('sends Idempotency-Key header when provided', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'PHP',
    ], idempotencyKey: 'idem_test_123');

    Http::assertSent(fn ($request) => $request->hasHeader('Idempotency-Key', 'idem_test_123'));
});

it('does not send Idempotency-Key header when null', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'PHP',
    ]);

    Http::assertSent(fn ($request) => ! $request->hasHeader('Idempotency-Key'));
});

it('returns null for getLastResponse before any request', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    expect($client->getLastResponse())->toBeNull();
});

it('captures response metadata after a successful request', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(
        loadFixture('payment_intent/created.json'),
        200,
        ['X-Request-Id' => 'req_abc123'],
    )]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->paymentIntents()->retrieve('pi_123');

    $metadata = $client->getLastResponse();

    expect($metadata)->not->toBeNull()
        ->and($metadata->statusCode)->toBe(200)
        ->and($metadata->header('X-Request-Id'))->toBe('req_abc123')
        ->and($metadata->header('x-request-id'))->toBe('req_abc123')
        ->and($metadata->header('Nonexistent-Header'))->toBeNull();
});

it('captures response metadata after a failed request', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(
        loadFixture('errors/invalid_request.json'),
        400,
        ['X-Request-Id' => 'req_err456'],
    )]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    expect(fn () => $client->paymentIntents()->create(['amount' => 10000, 'currency' => 'PHP']))
        ->toThrow(InvalidRequestException::class);

    $metadata = $client->getLastResponse();

    expect($metadata)->not->toBeNull()
        ->and($metadata->statusCode)->toBe(400)
        ->and($metadata->header('X-Request-Id'))->toBe('req_err456');
});

it('preserves exception details across all exception types', function () {
    $body = loadFixture('errors/invalid_request.json');

    $exceptions = [
        AuthenticationException::fromResponse($body, 401),
        InvalidRequestException::fromResponse($body, 400),
        ResourceNotFoundException::fromResponse($body, 404),
        RateLimitException::fromResponse($body, 429),
        PayrexApiException::fromResponse($body, 422),
    ];

    foreach ($exceptions as $exception) {
        expect($exception->errors)->toBe($body['errors'])
            ->and($exception->body)->toBe($body)
            ->and($exception->statusCode)->toBeGreaterThan(0);
    }
});
