<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\PaymentIntentAmountCapturable;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/payment_intent.amount_capturable.json');
    $event = new PaymentIntentAmountCapturable($payload);

    /** @var PaymentIntent $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(PaymentIntent::class)
        ->and($data->id)->toBe('pi_xxxxx')
        ->and($data->amount)->toBe(10000)
        ->and($data->amountReceived)->toBe(0)
        ->and($data->amountCapturable)->toBe(10000)
        ->and($data->currency)->toBe('PHP')
        ->and($data->status)->toBe(PaymentIntentStatus::AwaitingCapture)
        ->and($data->description)->toBeNull()
        ->and($data->metadata)->toBeNull()
        ->and($data->paymentMethods)->toBe(['card'])
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->nextAction)->toBeNull()
        ->and($data->returnUrl)->toBe('https://checkout.payrexhq.com/c/cs_xxxxx_secret_xxxxx')
        ->and($data->latestPayment)->toBeNull();
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/payment_intent.amount_capturable.json');
    $event = new PaymentIntentAmountCapturable($payload);

    expect($event->eventType())->toBe(WebhookEventType::PaymentIntentAmountCapturable);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/payment_intent.amount_capturable.json');
    $event = new PaymentIntentAmountCapturable($payload);

    expect($event->payload['id'])->toBe('evt_xxxxx')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/payment_intent.amount_capturable.json');
    $event = new PaymentIntentAmountCapturable($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'status' => 'processing',
            'amount_capturable' => 0,
        ]);
});
