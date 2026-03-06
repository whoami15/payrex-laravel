<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Exceptions\AuthenticationException;
use LegionHQ\LaravelPayrex\Exceptions\InvalidRequestException;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Exceptions\RateLimitException;
use LegionHQ\LaravelPayrex\Exceptions\ResourceNotFoundException;
use LegionHQ\LaravelPayrex\PayrexTransport;

it('sends a GET request with correct method, URL, and auth header', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents/pi_123');

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && $request->url() === 'https://api.payrexhq.com/payment_intents/pi_123'
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('sk_test_123:'))
    );
});

it('sends a POST request with correct method and data', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('POST', '/payment_intents', ['amount' => 10000]);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request->url() === 'https://api.payrexhq.com/payment_intents'
        && $request['amount'] === 10000
    );
});

it('sends a PUT request with correct method', function () {
    Http::fake(['https://api.payrexhq.com/payments/pay_123' => Http::response(loadFixture('payment/retrieved.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('PUT', '/payments/pay_123', ['description' => 'Updated']);

    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && $request->url() === 'https://api.payrexhq.com/payments/pay_123'
    );
});

it('sends a DELETE request with correct method', function () {
    Http::fake(['https://api.payrexhq.com/customers/cus_123' => Http::response(['deleted' => true])]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('DELETE', '/customers/cus_123');

    Http::assertSent(fn ($request) => $request->method() === 'DELETE'
        && $request->url() === 'https://api.payrexhq.com/customers/cus_123'
    );
});

it('returns decoded JSON array on successful response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $result = $transport->request('GET', '/payment_intents/pi_123');

    expect($result)
        ->toBeArray()
        ->and($result['id'])->toBe('pi_xxxxx')
        ->and($result['resource'])->toBe('payment_intent');
});

it('throws InvalidRequestException on 400 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('errors/invalid_request.json'), 400)]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('POST', '/payment_intents', []);
})->throws(InvalidRequestException::class);

it('throws AuthenticationException on 401 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('errors/authentication.json'), 401)]);

    $transport = new PayrexTransport('sk_test_invalid', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents');
})->throws(AuthenticationException::class);

it('throws ResourceNotFoundException on 404 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_nonexistent' => Http::response(loadFixture('errors/resource_not_found.json'), 404)]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents/pi_nonexistent');
})->throws(ResourceNotFoundException::class);

it('throws RateLimitException on 429 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(['errors' => [['detail' => 'Rate limit exceeded']]], 429)]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents');
})->throws(RateLimitException::class);

it('throws PayrexApiException on 500 response', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(['errors' => [['detail' => 'Internal server error']]], 500)]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents');
})->throws(PayrexApiException::class);

it('accepts custom timeout and connect timeout configuration', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 60, 10, 0, 100);

    $result = $transport->request('GET', '/payment_intents/pi_123');

    expect($result)->toBeArray()
        ->and($result['id'])->toBe('pi_xxxxx');
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

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 3, 0);

    $result = $transport->request('GET', '/payment_intents/pi_123');

    expect($result['id'])->toBe('pi_xxxxx')
        ->and($attempts)->toBe(3);
});

it('does not retry on client errors', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(loadFixture('errors/invalid_request.json'), 400);
    });

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 3, 0);

    expect(fn () => $transport->request('POST', '/payment_intents', []))
        ->toThrow(InvalidRequestException::class);

    expect($attempts)->toBe(1);
});

it('throws after all retries are exhausted', function () {
    $attempts = 0;

    Http::fake(function () use (&$attempts) {
        $attempts++;

        return Http::response(['errors' => [['detail' => 'Internal server error']]], 500);
    });

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 3, 0);

    expect(fn () => $transport->request('GET', '/payment_intents'))
        ->toThrow(function (PayrexApiException $e) {
            expect($e->statusCode)->toBe(500)
                ->and($e->getMessage())->toBe('Internal server error');
        });

    expect($attempts)->toBe(3);
});

it('sends User-Agent header with every request', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents/pi_123');

    Http::assertSent(fn ($request) => $request->hasHeader('User-Agent', 'laravel-payrex php/'.PHP_VERSION));
});

it('captures response metadata after a successful request', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_123' => Http::response(
        loadFixture('payment_intent/created.json'),
        200,
        ['X-Request-Id' => 'req_abc123'],
    )]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('GET', '/payment_intents/pi_123');

    $metadata = $transport->getLastResponse();

    expect($metadata)->not->toBeNull()
        ->and($metadata->statusCode)->toBe(200)
        ->and($metadata->header('X-Request-Id'))->toBe('req_abc123');
});

it('throws on unsupported HTTP method', function () {
    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('PATCH', '/payment_intents/pi_123');
})->throws(InvalidArgumentException::class, 'Unsupported HTTP method: PATCH');

it('encodes top-level scalar arrays with empty bracket notation', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('POST', '/payment_intents', [
        'amount' => 10000,
        'payment_methods' => ['card', 'gcash'],
    ]);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return str_contains($body, 'payment_methods%5B%5D=card')
            && str_contains($body, 'payment_methods%5B%5D=gcash')
            && ! preg_match('/payment_methods%5B\d+%5D/', $body);
    });
});

it('encodes nested scalar arrays with empty bracket notation', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements' => Http::response(loadFixture('billing_statement/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('POST', '/billing_statements', [
        'customer_id' => 'cus_123',
        'currency' => 'PHP',
        'payment_settings' => [
            'payment_methods' => ['card', 'gcash'],
        ],
    ]);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return str_contains($body, urlencode('payment_settings[payment_methods][]').'=card')
            && str_contains($body, urlencode('payment_settings[payment_methods][]').'=gcash')
            && ! preg_match('/payment_settings%5Bpayment_methods%5D%5B\d+%5D/', $body);
    });
});

it('encodes arrays of objects with empty bracket notation', function () {
    Http::fake(['https://api.payrexhq.com/checkout_sessions' => Http::response(loadFixture('checkout_session/created.json'))]);

    $transport = new PayrexTransport('sk_test_123', 'https://api.payrexhq.com', 30, 30, 0, 100);
    $transport->request('POST', '/checkout_sessions', [
        'currency' => 'PHP',
        'line_items' => [
            ['name' => 'Item A', 'amount' => 10000, 'quantity' => 1],
            ['name' => 'Item B', 'amount' => 20000, 'quantity' => 2],
        ],
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
    ]);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return str_contains($body, urlencode('line_items[][name]').'=Item+A')
            && str_contains($body, urlencode('line_items[][name]').'=Item+B');
    });
});
