<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementVoided;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement.voided.json');
    $event = new BillingStatementVoided($payload);

    /** @var BillingStatement $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatement::class)
        ->and($data->id)->toBe('bstm_xxxxx')
        ->and($data->amount)->toBe(50000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->customerId)->toBe('cus_xxxxx')
        ->and($data->status)->toBe(BillingStatementStatus::Void)
        ->and($data->description)->toBe('Updated description')
        ->and($data->billingStatementUrl)->not->toBeNull()
        ->and($data->billingStatementNumber)->toBe('7Z5VRJUI-0001')
        ->and($data->billingStatementMerchantName)->toBeNull()
        ->and($data->dueAt)->toBe(1774405313)
        ->and($data->finalizedAt)->toBeGreaterThan(0)
        ->and($data->finalizedAt)->toBe(1773800524)
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->lineItems)->toBeArray()
        ->and($data->lineItems)->toHaveCount(1)
        ->and($data->customer)->toBeInstanceOf(Customer::class)
        ->and($data->customer->id)->toBe('cus_xxxxx')
        ->and($data->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($data->paymentIntent->id)->toBe('pi_xxxxx')
        ->and($data->paymentIntent->status)->toBe(PaymentIntentStatus::Canceled)
        ->and($data->paymentSettings)->toBe(['payment_methods' => ['card']]);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement.voided.json');
    $event = new BillingStatementVoided($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementVoided);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement.voided.json');
    $event = new BillingStatementVoided($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/billing_statement.voided.json');
    $event = new BillingStatementVoided($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'status' => 'open',
        ]);
});
