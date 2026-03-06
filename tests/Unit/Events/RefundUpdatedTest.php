<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\RefundUpdated;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/refund.updated.json');
    $event = new RefundUpdated($payload);

    /** @var Refund $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(Refund::class)
        ->and($data->id)->toBe('re_xxxxx')
        ->and($data->amount)->toBe(10000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->status)->toBe(RefundStatus::Succeeded)
        ->and($data->reason)->toBe(RefundReason::RequestedByCustomer)
        ->and($data->description)->toBeNull()
        ->and($data->remarks)->toBeNull()
        ->and($data->paymentId)->toBe('pay_xxxxx')
        ->and($data->metadata)->toBe(['reason_detail' => 'customer requested']);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/refund.updated.json');
    $event = new RefundUpdated($payload);

    expect($event->eventType())->toBe(WebhookEventType::RefundUpdated);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/refund.updated.json');
    $event = new RefundUpdated($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
