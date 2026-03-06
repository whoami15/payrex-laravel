<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('webhook/created.json');
    $webhook = WebhookEndpoint::from($data);

    expect($webhook->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($webhook->resource)->toBe('webhook')
        ->and($webhook->secretKey)->toBe('whsk_cU8kMThbLEkF3yvz1ygCrPrBdAWguuCU')
        ->and($webhook->url)->toBe('https://my-ecommerce.com/send-shipments')
        ->and($webhook->events)->toBe(['payment_intent.succeeded'])
        ->and($webhook->description)->toBe('This is the webhook used for sending shipments after receiving successfully paid payments')
        ->and($webhook->livemode)->toBeFalse();
});

it('casts status to WebhookEndpointStatus enum', function () {
    expect((WebhookEndpoint::from(['id' => 'wh_1', 'resource' => 'webhook', 'status' => 'enabled']))->status)->toBe(WebhookEndpointStatus::Enabled)
        ->and((WebhookEndpoint::from(['id' => 'wh_2', 'resource' => 'webhook', 'status' => 'disabled']))->status)->toBe(WebhookEndpointStatus::Disabled);
});

it('returns null for unknown status values', function () {
    $webhook = WebhookEndpoint::from(['id' => 'wh_1', 'resource' => 'webhook', 'status' => 'nonexistent']);

    expect($webhook->status)->toBeNull();
});
