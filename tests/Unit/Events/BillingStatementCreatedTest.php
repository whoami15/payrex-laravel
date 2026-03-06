<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementCreated;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement.created.json');
    $event = new BillingStatementCreated($payload);

    /** @var BillingStatement $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatement::class)
        ->and($data->id)->toBe('bstm_jbvrBvJSTsyCDsYTMpyM3o8hEGdeX4tC')
        ->and($data->amount)->toBe(0)
        ->and($data->currency)->toBe('PHP')
        ->and($data->customerId)->toBe('cus_vhFA73Qu3M6GQwhRwEVgAX9aFZUAxeYD')
        ->and($data->status)->toBe(BillingStatementStatus::Draft)
        ->and($data->description)->toBe('Test billing statement')
        ->and($data->billingStatementUrl)->toBe('https://bill.payrexhq.com/b/test_bstm_jbvrBvJSTsyCDsYTMpyM3o8hEGdeX4tC_secret_S1MNRpTH2MMvLcoBNu6KTpevmogbrfrU2YLMxZ47uT6wpBrMTo')
        ->and($data->billingStatementNumber)->toBe('UAEONUMZ-0001')
        ->and($data->billingStatementMerchantName)->toBeNull()
        ->and($data->dueAt)->toBe(0)
        ->and($data->finalizedAt)->toBe(0)
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->lineItems)->toBeArray()
        ->and($data->lineItems)->toHaveCount(0)
        ->and($data->customer)->toBeInstanceOf(Customer::class)
        ->and($data->customer->id)->toBe('cus_vhFA73Qu3M6GQwhRwEVgAX9aFZUAxeYD')
        ->and($data->paymentIntent)->toBeNull()
        ->and($data->paymentSettings)->toBe(['payment_methods' => ['card']]);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement.created.json');
    $event = new BillingStatementCreated($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementCreated);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement.created.json');
    $event = new BillingStatementCreated($payload);

    expect($event->payload['id'])->toBe('evt_yYCzrb76foeeZAQhu9nX3bnzE9MxNfW4')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
