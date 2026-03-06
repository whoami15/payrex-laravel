<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementDeleted;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement.deleted.json');
    $event = new BillingStatementDeleted($payload);

    /** @var BillingStatement $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatement::class)
        ->and($data->id)->toBe('bstm_xxxxx')
        ->and($data->amount)->toBe(50000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->customerId)->toBe('cus_xxxxx')
        ->and($data->status)->toBe(BillingStatementStatus::Draft)
        ->and($data->description)->toBe('Test billing statement')
        ->and($data->billingStatementUrl)->toBe('https://bill.payrexhq.com/b/test_bstm_xxxxx_secret_xxxxx')
        ->and($data->billingStatementNumber)->toBe('J2FVW1SV-0001')
        ->and($data->billingStatementMerchantName)->toBeNull()
        ->and($data->dueAt)->toBe(0)
        ->and($data->finalizedAt)->toBe(0)
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->lineItems)->toBeArray()
        ->and($data->lineItems)->toHaveCount(1)
        ->and($data->customer)->toBeInstanceOf(Customer::class)
        ->and($data->customer->id)->toBe('cus_xxxxx')
        ->and($data->paymentIntent)->toBeNull()
        ->and($data->paymentSettings)->toBe(['payment_methods' => ['card']]);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement.deleted.json');
    $event = new BillingStatementDeleted($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementDeleted);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement.deleted.json');
    $event = new BillingStatementDeleted($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
