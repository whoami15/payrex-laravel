<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\RefundCreated;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/refund.created.json');
    $event = new RefundCreated($payload);

    /** @var Refund $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(Refund::class)
        ->and($data->id)->toBe('re_D3gN2BpJLZvMTzbBCtARW298AcxNFDxx')
        ->and($data->amount)->toBe(10000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->status)->toBe(RefundStatus::Succeeded)
        ->and($data->reason)->toBe(RefundReason::RequestedByCustomer)
        ->and($data->description)->toBeNull()
        ->and($data->remarks)->toBeNull()
        ->and($data->paymentId)->toBe('pay_RBwW29aXGwQofjkgYhDeAuLt8u9Urp1U')
        ->and($data->metadata)->toBeNull();
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/refund.created.json');
    $event = new RefundCreated($payload);

    expect($event->eventType())->toBe(WebhookEventType::RefundCreated);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/refund.created.json');
    $event = new RefundCreated($payload);

    expect($event->payload['id'])->toBe('evt_LX3W6BLcgb2yGBUdm9Xjxu55JqB2FAZD')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});
