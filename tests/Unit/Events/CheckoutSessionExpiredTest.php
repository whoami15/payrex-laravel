<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\CheckoutSession;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\CheckoutSessionStatus;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\CheckoutSessionExpired;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/checkout_session.expired.json');
    $event = new CheckoutSessionExpired($payload);

    /** @var CheckoutSession $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(CheckoutSession::class)
        ->and($data->id)->toBe('cs_PJkxRnMQESSZSjCppBM5GQZi9EnxjjCG')
        ->and($data->status)->toBe(CheckoutSessionStatus::Expired)
        ->and($data->currency)->toBe('PHP')
        ->and($data->url)->not->toBeNull()
        ->and($data->url)->toBe('https://checkout.payrexhq.com/c/cs_PJkxRnMQESSZSjCppBM5GQZi9EnxjjCG_secret_9reUGbzz9LozruKAF2KtHJ71vDsFD8m8')
        ->and($data->lineItems)->toBeArray()
        ->and($data->lineItems)->toHaveCount(1)
        ->and($data->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($data->paymentIntent->id)->toBe('pi_GPAD63ZenWUXKmthpNuy5wg2xfer2REH')
        ->and($data->paymentIntent->status)->toBe(PaymentIntentStatus::Canceled)
        ->and($data->successUrl)->toBe('https://example.com/checkout/success')
        ->and($data->cancelUrl)->toBe('https://example.com/checkout/cancel')
        ->and($data->paymentMethods)->toBe(['card', 'maya', 'gcash', 'qrph'])
        ->and($data['capture_type'])->toBe('automatic')
        ->and($data->submitType)->toBe('pay');
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/checkout_session.expired.json');
    $event = new CheckoutSessionExpired($payload);

    expect($event->eventType())->toBe(WebhookEventType::CheckoutSessionExpired);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/checkout_session.expired.json');
    $event = new CheckoutSessionExpired($payload);

    expect($event->payload['id'])->toBe('evt_2PJVTCej4N1Jw566LiEq5CM5nfRN72Ch')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/checkout_session.expired.json');
    $event = new CheckoutSessionExpired($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'status' => 'active',
        ]);
});
