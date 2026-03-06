<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Facades\Payrex;
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

it('exposes all resources via the facade', function () {
    expect(Payrex::paymentIntents())->toBeInstanceOf(PaymentIntentResource::class)
        ->and(Payrex::payments())->toBeInstanceOf(PaymentResource::class)
        ->and(Payrex::refunds())->toBeInstanceOf(RefundResource::class)
        ->and(Payrex::customers())->toBeInstanceOf(CustomerResource::class)
        ->and(Payrex::checkoutSessions())->toBeInstanceOf(CheckoutSessionResource::class)
        ->and(Payrex::webhooks())->toBeInstanceOf(WebhookResource::class)
        ->and(Payrex::billingStatements())->toBeInstanceOf(BillingStatementResource::class)
        ->and(Payrex::billingStatementLineItems())->toBeInstanceOf(BillingStatementLineItemResource::class)
        ->and(Payrex::payoutTransactions())->toBeInstanceOf(PayoutTransactionResource::class);
});

it('can call resource methods through the facade', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $result = Payrex::customers()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(Customer::class);
});

it('can call resource methods through the client instance', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = app(PayrexClient::class);
    $result = $client->customers()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(Customer::class);
});
