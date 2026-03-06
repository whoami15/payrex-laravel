<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementMarkedUncollectible;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement.marked_uncollectible.json');
    $event = new BillingStatementMarkedUncollectible($payload);

    /** @var BillingStatement $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatement::class)
        ->and($data->id)->toBe('bstm_xxxxx')
        ->and($data->amount)->toBe(50000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->customerId)->toBe('cus_xxxxx')
        ->and($data->status)->toBe(BillingStatementStatus::Uncollectible)
        ->and($data->description)->toBe('Updated description')
        ->and($data->billingStatementUrl)->not->toBeNull()
        ->and($data->billingStatementNumber)->toBe('9FXNROGO-0001')
        ->and($data->billingStatementMerchantName)->toBeNull()
        ->and($data->dueAt)->toBe(1774402259)
        ->and($data->finalizedAt)->toBeGreaterThan(0)
        ->and($data->finalizedAt)->toBe(1773797474)
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
    $payload = loadFixture('webhooks/billing_statement.marked_uncollectible.json');
    $event = new BillingStatementMarkedUncollectible($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementMarkedUncollectible);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement.marked_uncollectible.json');
    $event = new BillingStatementMarkedUncollectible($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/billing_statement.marked_uncollectible.json');
    $event = new BillingStatementMarkedUncollectible($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'status' => 'open',
        ]);
});
