<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\PayrexObject;
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

function buildWebhookSignature(string $payload, string $secret, ?int $timestamp = null): string
{
    $timestamp = $timestamp ?? time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},te={$signature},li=";
}

it('sends a GET request with correct method, URL, and auth header', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents/pi_123');

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && $request->url() === 'https://api.payrexhq.com/payment_intents/pi_123'
        && $request->hasHeader('Authorization')
    );
});

it('sends a POST request with correct method and data', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->post('/payment_intents', ['amount' => 10000]);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request->url() === 'https://api.payrexhq.com/payment_intents'
        && $request['amount'] === 10000
    );
});

it('sends a PUT request with correct method', function () {
    Http::fake(['https://api.payrexhq.com/payments/pay_123' => Http::response(loadFixture('payment/retrieved.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->put('/payments/pay_123', ['description' => 'Updated']);

    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && $request->url() === 'https://api.payrexhq.com/payments/pay_123'
    );
});

it('sends a DELETE request with correct method', function () {
    Http::fake(['https://api.payrexhq.com/customers/cus_123' => Http::response(['deleted' => true])]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->delete('/customers/cus_123');

    Http::assertSent(fn ($request) => $request->method() === 'DELETE'
        && $request->url() === 'https://api.payrexhq.com/customers/cus_123'
    );
});

it('returns decoded JSON array on successful response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->get('/payment_intents/pi_123');

    expect($result)
        ->toBeArray()
        ->and($result['id'])->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($result['resource'])->toBe('payment_intent');
});

it('throws InvalidRequestException on 400 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('errors/invalid_request.json'), 400)]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->post('/payment_intents', []);
})->throws(InvalidRequestException::class);

it('throws AuthenticationException on 401 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('errors/authentication.json'), 401)]);

    $client = new PayrexClient(secretKey: 'sk_test_invalid', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents');
})->throws(AuthenticationException::class);

it('throws ResourceNotFoundException on 404 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_nonexistent' => Http::response(loadFixture('errors/resource_not_found.json'), 404)]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents/pi_nonexistent');
})->throws(ResourceNotFoundException::class);

it('throws RateLimitException on 429 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(['errors' => [['detail' => 'Rate limit exceeded']]], 429)]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents');
})->throws(RateLimitException::class);

it('throws PayrexApiException on 500 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(['errors' => [['detail' => 'Internal server error']]], 500)]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents');
})->throws(PayrexApiException::class);

it('uses custom timeout values', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.payrexhq.com',
        timeout: 60,
        connectTimeout: 10,
    );
    $client->get('/payment_intents/pi_123');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/payment_intents/pi_123'
        && $request->method() === 'GET'
    );
});

it('retries on server errors when retries configured', function () {
    $attempts = 0;

    Http::fake(function ($request) use (&$attempts) {
        $attempts++;

        if ($attempts < 3) {
            return Http::response(['errors' => [['detail' => 'Internal server error']]], 500);
        }

        return Http::response(loadFixture('payment_intent/created.json'));
    });

    $client = new PayrexClient(
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.payrexhq.com',
        retries: 3,
        retryDelay: 0,
    );

    $result = $client->get('/payment_intents/pi_123');

    expect($result['id'])->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($attempts)->toBe(3);
});

it('does not retry on client errors', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(loadFixture('errors/invalid_request.json'), 400);
    });

    $client = new PayrexClient(
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.payrexhq.com',
        retries: 3,
        retryDelay: 0,
    );

    try {
        $client->post('/payment_intents', []);
    } catch (InvalidRequestException) {
        // Expected
    }

    expect($attempts)->toBe(1);
});

it('throws after all retries are exhausted', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(['errors' => [['detail' => 'Internal server error']]], 500);
    });

    $client = new PayrexClient(
        secretKey: 'sk_test_123',
        baseUrl: 'https://api.payrexhq.com',
        retries: 3,
        retryDelay: 0,
    );

    try {
        $client->get('/payment_intents');
    } catch (PayrexApiException $e) {
        expect($e->statusCode)->toBe(500)
            ->and($e->getMessage())->toBe('Internal server error')
            ->and($attempts)->toBe(3);
    }
});

it('constructs a typed event from a valid webhook payload', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode([
        'id' => 'evt_123',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'pi_123', 'amount' => 50000]],
    ]);

    $header = buildWebhookSignature($payload, 'whsec_test');
    $event = $client->constructEvent($payload, $header, 'whsec_test');

    expect($event)
        ->toBeInstanceOf(PaymentIntentSucceeded::class)
        ->eventType()->toBe(WebhookEventType::PaymentIntentSucceeded)
        ->data()->toBeInstanceOf(PayrexObject::class)
        ->and($event->data()->id)->toBe('pi_123')
        ->and($event->data()['amount'])->toBe(50000)
        ->and($event->isLiveMode())->toBeFalse();
});

it('falls back to WebhookReceived for unknown event types', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    $payload = json_encode([
        'id' => 'evt_456',
        'type' => 'unknown.event',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'res_456']],
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

    $payload = json_encode(['type' => 'payment_intent.succeeded', 'data' => ['resource' => []]]);
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
        'data' => ['resource' => ['id' => 'pi_123', 'amount' => 50000]],
    ]);

    $header = buildWebhookSignature($payload, 'whsec_configured');
    $event = $client->constructEvent($payload, $header);

    expect($event)->toBeInstanceOf(PaymentIntentSucceeded::class);
});

it('sends User-Agent header with every request', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->get('/payment_intents/pi_123');

    Http::assertSent(fn ($request) => $request->hasHeader('User-Agent', 'laravel-payrex php/'.PHP_VERSION));
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
