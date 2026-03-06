<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\PayrexClient;
use LegionHQ\LaravelPayrex\Resources\BillingStatementResource;
use LegionHQ\LaravelPayrex\Resources\CheckoutSessionResource;
use LegionHQ\LaravelPayrex\Resources\CustomerResource;
use LegionHQ\LaravelPayrex\Resources\PaymentIntentResource;
use LegionHQ\LaravelPayrex\Resources\PaymentResource;
use LegionHQ\LaravelPayrex\Resources\PayoutTransactionResource;
use LegionHQ\LaravelPayrex\Resources\RefundResource;
use LegionHQ\LaravelPayrex\Resources\WebhookResource;

it('registers the payrex client as a singleton', function () {
    $client = app(PayrexClient::class);

    expect($client)->toBeInstanceOf(PayrexClient::class);
    expect(app(PayrexClient::class))->toBe($client);
});

it('resolves the payrex alias', function () {
    expect(app('payrex'))->toBeInstanceOf(PayrexClient::class);
});

it('has the payment intents resource', function () {
    $client = app(PayrexClient::class);

    expect($client->paymentIntents())
        ->toBeInstanceOf(PaymentIntentResource::class);
});

it('has the payments resource', function () {
    $client = app(PayrexClient::class);

    expect($client->payments())
        ->toBeInstanceOf(PaymentResource::class);
});

it('has the refunds resource', function () {
    $client = app(PayrexClient::class);

    expect($client->refunds())
        ->toBeInstanceOf(RefundResource::class);
});

it('has the customers resource', function () {
    $client = app(PayrexClient::class);

    expect($client->customers())
        ->toBeInstanceOf(CustomerResource::class);
});

it('has the checkout sessions resource', function () {
    $client = app(PayrexClient::class);

    expect($client->checkoutSessions())
        ->toBeInstanceOf(CheckoutSessionResource::class);
});

it('has the webhooks resource', function () {
    $client = app(PayrexClient::class);

    expect($client->webhooks())
        ->toBeInstanceOf(WebhookResource::class);
});

it('has the billing statements resource', function () {
    $client = app(PayrexClient::class);

    expect($client->billingStatements())
        ->toBeInstanceOf(BillingStatementResource::class);
});

it('has the payout transactions resource', function () {
    $client = app(PayrexClient::class);

    expect($client->payoutTransactions())
        ->toBeInstanceOf(PayoutTransactionResource::class);
});
