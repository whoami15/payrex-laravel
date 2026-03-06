<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\PayrexClient;
use LegionHQ\LaravelPayrex\Resources\BillingStatementLineItemResource;
use LegionHQ\LaravelPayrex\Resources\BillingStatementResource;
use LegionHQ\LaravelPayrex\Resources\CheckoutSessionResource;
use LegionHQ\LaravelPayrex\Resources\CustomerResource;
use LegionHQ\LaravelPayrex\Resources\PaymentIntentResource;
use LegionHQ\LaravelPayrex\Resources\PaymentResource;
use LegionHQ\LaravelPayrex\Resources\PayoutTransactionResource;
use LegionHQ\LaravelPayrex\Resources\RefundResource;
use LegionHQ\LaravelPayrex\Resources\WebhookResource;

it('initializes all nine resource methods', function () {
    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');

    expect($client->paymentIntents())->toBeInstanceOf(PaymentIntentResource::class)
        ->and($client->payments())->toBeInstanceOf(PaymentResource::class)
        ->and($client->refunds())->toBeInstanceOf(RefundResource::class)
        ->and($client->customers())->toBeInstanceOf(CustomerResource::class)
        ->and($client->checkoutSessions())->toBeInstanceOf(CheckoutSessionResource::class)
        ->and($client->webhooks())->toBeInstanceOf(WebhookResource::class)
        ->and($client->billingStatements())->toBeInstanceOf(BillingStatementResource::class)
        ->and($client->billingStatementLineItems())->toBeInstanceOf(BillingStatementLineItemResource::class)
        ->and($client->payoutTransactions())->toBeInstanceOf(PayoutTransactionResource::class);
});

it('throws on empty secret key', function () {
    new PayrexClient(secretKey: '');
})->throws(InvalidArgumentException::class, 'PayRex secret key cannot be empty.');

it('throws on empty base URL', function () {
    new PayrexClient(secretKey: 'sk_test_123', baseUrl: '');
})->throws(InvalidArgumentException::class, 'PayRex API base URL cannot be empty.');
