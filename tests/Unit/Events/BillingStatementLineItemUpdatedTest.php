<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemUpdated;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.updated.json');
    $event = new BillingStatementLineItemUpdated($payload);

    /** @var BillingStatementLineItem $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($data->id)->toBe('bstm_li_vSHaVoXNzQpMqMumS5F93vFsSyggmxg6')
        ->and($data->description)->toBe('Updated Line Item')
        ->and($data->unitPrice)->toBe(75000)
        ->and($data->quantity)->toBe(1)
        ->and($data->billingStatementId)->toBe('bstm_jbvrBvJSTsyCDsYTMpyM3o8hEGdeX4tC');
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.updated.json');
    $event = new BillingStatementLineItemUpdated($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementLineItemUpdated);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.updated.json');
    $event = new BillingStatementLineItemUpdated($payload);

    expect($event->payload['id'])->toBe('evt_BRqNAevRB3dgFSG1Vk6j5ZbpiUTWVW78')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.updated.json');
    $event = new BillingStatementLineItemUpdated($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'quantity' => 1,
            'unit_price' => 50000,
            'description' => 'Line Item 1',
        ]);
});
