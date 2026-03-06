<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\PayrexClient;

it('applies default currency when not provided', function () {
    Http::fake(['*' => Http::response(loadFixture('payment_intent/created.json'))]);

    config(['payrex.currency' => 'PHP']);

    $client = app(PayrexClient::class);
    $client->paymentIntents()->create([
        'amount' => 10000,
        'payment_methods' => ['card'],
    ]);

    Http::assertSent(function ($r) {
        return $r['currency'] === 'PHP';
    });
});

it('does not override explicitly provided currency', function () {
    Http::fake(['*' => Http::response(loadFixture('payment_intent/created.json'))]);

    config(['payrex.currency' => 'PHP']);

    $client = app(PayrexClient::class);
    $client->paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'USD',
        'payment_methods' => ['card'],
    ]);

    Http::assertSent(function ($r) {
        return $r['currency'] === 'USD';
    });
});

it('applies default currency to refund create', function () {
    Http::fake(['*' => Http::response(loadFixture('refund/created.json'))]);

    config(['payrex.currency' => 'PHP']);

    $client = app(PayrexClient::class);
    $client->refunds()->create([
        'payment_id' => 'pay_test_123',
        'amount' => 5000,
        'reason' => 'requested_by_customer',
    ]);

    Http::assertSent(function ($r) {
        return $r['currency'] === 'PHP';
    });
});

it('applies default currency to checkout session create', function () {
    Http::fake(['*' => Http::response(loadFixture('checkout_session/created.json'))]);

    config(['payrex.currency' => 'PHP']);

    $client = app(PayrexClient::class);
    $client->checkoutSessions()->create([
        'line_items' => [['name' => 'Test', 'amount' => 1000, 'quantity' => 1]],
        'success_url' => 'https://example.com/success',
        'cancel_url' => 'https://example.com/cancel',
    ]);

    Http::assertSent(function ($r) {
        return $r['currency'] === 'PHP';
    });
});

it('applies default currency to customer create', function () {
    Http::fake(['*' => Http::response(loadFixture('customer/created.json'))]);

    config(['payrex.currency' => 'PHP']);

    $client = app(PayrexClient::class);
    $client->customers()->create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
    ]);

    Http::assertSent(function ($r) {
        return $r['currency'] === 'PHP';
    });
});
