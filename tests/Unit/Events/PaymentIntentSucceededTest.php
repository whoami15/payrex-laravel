<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\Enums\PaymentStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;

it('returns event data as a typed DTO via data()', function () {
    $payload = loadFixture('webhooks/payment_intent.succeeded.json');
    $event = new PaymentIntentSucceeded($payload);

    /** @var PaymentIntent $data */
    $data = $event->data();

    expect($data)
        ->toBeInstanceOf(PaymentIntent::class)
        ->and($data->id)->toBe('pi_ktqK8fURW7a4d18mMP9ZbbB9SRM6nu7w')
        ->and($data->amount)->toBe(10000)
        ->and($data->amountReceived)->toBe(10000)
        ->and($data->amountCapturable)->toBe(0)
        ->and($data->currency)->toBe('PHP')
        ->and($data->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($data->description)->toBeNull()
        ->and($data->metadata)->toBeNull()
        ->and($data->paymentMethods)->toBe(['card', 'gcash', 'maya', 'qrph'])
        ->and($data->statementDescriptor)->toBeNull()
        ->and($data->nextAction)->toBeNull()
        ->and($data->returnUrl)->toBe('https://checkout.payrexhq.com/c/cs_FNcLcStQs6kifRfxoDkZETqHDmhY6As8_secret_i4GntbVgJJRu4GJuaSgmbZ7EHGMVMHpF')
        ->and($data->latestPayment)->toBeInstanceOf(Payment::class);
});

it('returns the correct event type enum', function () {
    $payload = loadFixture('webhooks/payment_intent.succeeded.json');
    $event = new PaymentIntentSucceeded($payload);

    expect($event->eventType())->toBe(WebhookEventType::PaymentIntentSucceeded);
});

it('exposes the full payload and metadata', function () {
    $payload = loadFixture('webhooks/payment_intent.succeeded.json');
    $event = new PaymentIntentSucceeded($payload);

    expect($event->payload['id'])->toBe('evt_fMzKaDnPGT37irVyhVHukPgLPePNV3Un')
        ->and($event->payload['resource'])->toBe('event')
        ->and($event->isLiveMode())->toBeFalse();
});

it('hydrates expanded latest_payment as a Payment DTO via data()', function () {
    $payload = loadFixture('webhooks/payment_intent.succeeded.json');
    $event = new PaymentIntentSucceeded($payload);

    /** @var PaymentIntent $data */
    $data = $event->data();

    /** @var Payment $latestPayment */
    $latestPayment = $data->latestPayment;

    expect($latestPayment)
        ->toBeInstanceOf(Payment::class)
        ->and($latestPayment->id)->toBe('pay_RBwW29aXGwQofjkgYhDeAuLt8u9Urp1U')
        ->and($latestPayment->amount)->toBe(10000)
        ->and($latestPayment->status)->toBe(PaymentStatus::Paid);
});

it('exposes previous_attributes at the root level of the payload', function () {
    $payload = loadFixture('webhooks/payment_intent.succeeded.json');
    $event = new PaymentIntentSucceeded($payload);

    expect($event->payload['previous_attributes'])
        ->toBe([
            'status' => 'processing',
            'amount_capturable' => 0,
            'amount_received' => 0,
        ]);
});
