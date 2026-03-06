<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementWillBeDue;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement.will_be_due.json');
    $event = new BillingStatementWillBeDue($payload);

    /** @var BillingStatement $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatement::class)
        ->and($data->id)->toBe('bstm_xxxxx')
        ->and($data->amount)->toBe(50000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->customerId)->toBe('cus_xxxxx')
        ->and($data->status)->toBe(BillingStatementStatus::Open)
        ->and($data->description)->toBe('Updated description')
        ->and($data->billingStatementUrl)->toStartWith('https://bill.payrexhq.com/')
        ->and($data->billingStatementNumber)->toBe('A3EAXLGV-0001')
        ->and($data->billingStatementMerchantName)->toBeNull()
        ->and($data->dueAt)->toBe(1774403005)
        ->and($data->finalizedAt)->toBe(1773798216)
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->lineItems)->toHaveCount(1)
        ->and($data->lineItems[0])->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($data->lineItems[0]->description)->toBe('Line Item 1')
        ->and($data->lineItems[0]->unitPrice)->toBe(50000)
        ->and($data->lineItems[0]->quantity)->toBe(1)
        ->and($data->customer)->toBeInstanceOf(Customer::class)
        ->and($data->customer->id)->toBe('cus_xxxxx')
        ->and($data->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($data->paymentIntent->id)->toBe('pi_xxxxx')
        ->and($data->paymentSettings)->toBe(['payment_methods' => ['card']]);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement.will_be_due.json');
    $event = new BillingStatementWillBeDue($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementWillBeDue);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement.will_be_due.json');
    $event = new BillingStatementWillBeDue($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
