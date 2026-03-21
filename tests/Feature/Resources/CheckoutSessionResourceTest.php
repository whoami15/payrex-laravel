<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\CheckoutSession;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\CheckoutSessionStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a checkout session', function () {
    Http::fake(['https://api.payrexhq.com/checkout_sessions' => Http::response(loadFixture('checkout_session/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->checkoutSessions()->create([
        'currency' => 'PHP',
        'line_items' => [
            [
                'name' => 'Some name',
                'amount' => 10000,
                'quantity' => 5,
                'image' => 'https://some-url.com/1.jpg',
            ],
            [
                'name' => 'Some name',
                'amount' => 10000,
                'quantity' => 5,
            ],
        ],
        'success_url' => 'http://some-url.com',
        'cancel_url' => 'http://some-url.com',
        'payment_methods' => ['card', 'gcash'],
        'description' => 'Some description',
        'billing_details_collection' => 'always',
        'submit_type' => 'pay',
        'payment_method_options' => [
            'card' => [
                'capture_type' => 'automatic',
            ],
        ],
    ]);

    expect($result)->toBeInstanceOf(CheckoutSession::class)
        ->and($result->id)->toBe('cs_xxxxx')
        ->and($result->resource)->toBe('checkout_session')
        ->and($result->amount)->toBe(100000)
        ->and($result->currency)->toBe('PHP')
        ->and($result->status)->toBe(CheckoutSessionStatus::Active)
        ->and($result->url)->toBe('https://checkout.payrexhq.com/c/cs_xxxxx_secret_xxxxx')
        ->and($result->clientSecret)->toBe('cs_xxxxx_secret_xxxxx')
        ->and($result->customerId)->toBeNull()
        ->and($result->customer)->toBeNull()
        ->and($result->customerReferenceId)->toBeNull()
        ->and($result->description)->toBe('Some description')
        ->and($result->successUrl)->toBe('http://some-url.com')
        ->and($result->cancelUrl)->toBe('http://some-url.com')
        ->and($result->billingDetailsCollection)->toBe('always')
        ->and($result->submitType)->toBe('pay')
        ->and($result->statementDescriptor)->toBe('Override statement descriptor')
        ->and($result->expiresAt)->toBe(1721813375)
        ->and($result->paymentIntent)->not->toBeNull()
        ->and($result['payment_intent']['id'])->toBe('pi_xxxxx')
        ->and($result->paymentMethods)->toBe(['card', 'gcash'])
        ->and($result['line_items'])->toHaveCount(2)
        ->and($result['line_items'][0]['name'])->toBe('Some name')
        ->and($result['line_items'][0]['resource'])->toBe('checkout_session_line_item')
        ->and($result['line_items'][1]['image'])->toBeNull()
        ->and($result->metadata)->toBeNull()
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/checkout_sessions'
            && $r->method() === 'POST'
            && $r['currency'] === 'PHP'
            && $r['success_url'] === 'http://some-url.com'
            && $r['cancel_url'] === 'http://some-url.com';
    });
});

it('retrieves a checkout session', function () {
    Http::fake(['https://api.payrexhq.com/checkout_sessions/cs_xxxxx' => Http::response(loadFixture('checkout_session/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->checkoutSessions()->retrieve('cs_xxxxx');

    expect($result)->toBeInstanceOf(CheckoutSession::class)
        ->and($result->id)->toBe('cs_xxxxx')
        ->and($result->status)->toBe(CheckoutSessionStatus::Active)
        ->and($result->url)->toBe('https://checkout.payrexhq.com/c/cs_xxxxx_secret_xxxxx')
        ->and($result->paymentIntent)->not->toBeNull();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/checkout_sessions/cs_xxxxx'
        && $r->method() === 'GET'
    );
});

it('creates a checkout session with a customer', function () {
    Http::fake(['https://api.payrexhq.com/checkout_sessions' => Http::response(loadFixture('checkout_session/created_with_customer.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->checkoutSessions()->create([
        'line_items' => [['name' => 'Test Product', 'amount' => 10000, 'quantity' => 1]],
        'payment_methods' => ['card'],
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
        'customer_id' => 'cus_xxxxx',
    ]);

    expect($result)->toBeInstanceOf(CheckoutSession::class)
        ->and($result->id)->toBe('cs_xxxxx')
        ->and($result->customerId)->toBe('cus_xxxxx')
        // The API returns a partial customer expansion (no id/resource),
        // so expandRelation treats it as a valid array and constructs a
        // Customer DTO with a null id
        ->and($result->customer)->toBeInstanceOf(Customer::class)
        ->and($result->customer->id)->toBeNull()
        ->and($result->customer->name)->toBe('Test Customer')
        ->and($result->customer->email)->toBe('test@example.com')
        // The nested payment_intent.customer has a full expansion (with id)
        ->and($result->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($result->paymentIntent->customer)->toBeInstanceOf(Customer::class)
        ->and($result->paymentIntent->customer->id)->toBe('cus_xxxxx')
        ->and($result->paymentIntent->customer->name)->toBe('Test Customer');

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/checkout_sessions'
            && $r->method() === 'POST'
            && $r['customer_id'] === 'cus_xxxxx';
    });
});

it('expires a checkout session', function () {
    Http::fake(['https://api.payrexhq.com/checkout_sessions/cs_xxxxx/expire' => Http::response(loadFixture('checkout_session/expired.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->checkoutSessions()->expire('cs_xxxxx');

    expect($result)->toBeInstanceOf(CheckoutSession::class)
        ->and($result->id)->toBe('cs_xxxxx')
        ->and($result->status)->toBe(CheckoutSessionStatus::Expired);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/checkout_sessions/cs_xxxxx/expire'
        && $r->method() === 'POST'
    );
});
