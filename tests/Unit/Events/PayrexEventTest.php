<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Events\BillingStatementCreated;
use LegionHQ\LaravelPayrex\Events\PaymentIntentAmountCapturable;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
use LegionHQ\LaravelPayrex\Events\WebhookReceived;
use LegionHQ\LaravelPayrex\Exceptions\PayrexException;

it('returns null for eventType when type is missing', function () {
    $event = new PaymentIntentSucceeded([]);

    expect($event->eventType())->toBeNull();
});

it('returns null for eventType when type is unknown', function () {
    $event = new PaymentIntentSucceeded(['type' => 'unknown.event']);

    expect($event->eventType())->toBeNull();
});

it('returns false for isLiveMode in test mode', function () {
    $event = new PaymentIntentSucceeded(['livemode' => false]);

    expect($event->isLiveMode())->toBeFalse();
});

it('returns true for isLiveMode when livemode is true', function () {
    $event = new PaymentIntentSucceeded(['livemode' => true]);

    expect($event->isLiveMode())->toBeTrue();
});

it('resolves known event types to their correct event class via constructFrom', function () {
    $succeeded = PayrexEvent::constructFrom(['type' => 'payment_intent.succeeded', 'data' => ['id' => 'pi_1', 'resource' => 'payment_intent']]);
    $capturable = PayrexEvent::constructFrom(['type' => 'payment_intent.amount_capturable', 'data' => ['id' => 'pi_2', 'resource' => 'payment_intent']]);
    $billing = PayrexEvent::constructFrom(['type' => 'billing_statement.created', 'data' => ['id' => 'bstm_1', 'resource' => 'billing_statement']]);

    expect($succeeded)->toBeInstanceOf(PaymentIntentSucceeded::class)
        ->and($capturable)->toBeInstanceOf(PaymentIntentAmountCapturable::class)
        ->and($billing)->toBeInstanceOf(BillingStatementCreated::class);
});

it('falls back to WebhookReceived for unknown event types via constructFrom', function () {
    $unknown = PayrexEvent::constructFrom(['type' => 'unknown.event', 'data' => ['id' => 'x_1', 'resource' => 'unknown']]);
    $empty = PayrexEvent::constructFrom(['type' => '', 'data' => ['id' => 'x_2', 'resource' => 'unknown']]);

    expect($unknown)->toBeInstanceOf(WebhookReceived::class)
        ->and($empty)->toBeInstanceOf(WebhookReceived::class);
});

it('throws when data is missing from payload', function () {
    $event = new PaymentIntentSucceeded(['type' => 'payment_intent.succeeded']);

    $event->data();
})->throws(PayrexException::class, 'Webhook payload is missing the expected "data" structure.');

it('throws when data is empty from payload', function () {
    $event = new PaymentIntentSucceeded([
        'type' => 'payment_intent.succeeded',
        'data' => [],
    ]);

    $event->data();
})->throws(PayrexException::class, 'Webhook payload is missing the expected "data" structure.');

it('throws when data is not an array', function () {
    $event = new PaymentIntentSucceeded([
        'type' => 'payment_intent.succeeded',
        'data' => 'not-an-array',
    ]);

    $event->data();
})->throws(PayrexException::class, 'Webhook payload is missing the expected "data" structure.');
