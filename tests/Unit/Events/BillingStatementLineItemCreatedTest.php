<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemCreated;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.created.json');
    $event = new BillingStatementLineItemCreated($payload);

    /** @var BillingStatementLineItem $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($data->id)->toBe('bstm_li_vSHaVoXNzQpMqMumS5F93vFsSyggmxg6')
        ->and($data->description)->toBe('Line Item 1')
        ->and($data->unitPrice)->toBe(50000)
        ->and($data->quantity)->toBe(1)
        ->and($data->billingStatementId)->toBe('bstm_jbvrBvJSTsyCDsYTMpyM3o8hEGdeX4tC');
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.created.json');
    $event = new BillingStatementLineItemCreated($payload);

    expect($event->eventType())->toBe(WebhookEventType::BillingStatementLineItemCreated);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/billing_statement_line_item.created.json');
    $event = new BillingStatementLineItemCreated($payload);

    expect($event->payload['id'])->toBe('evt_rEMe8nHanKozoKXHwkMGjc4TekZi3kCn')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
