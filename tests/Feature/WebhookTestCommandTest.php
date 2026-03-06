<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\WebhookReceived;

it('dispatches events for valid event type', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.succeeded'])
        ->assertSuccessful()
        ->expectsOutputToContain('Dispatched payment_intent.succeeded event successfully.');

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(PaymentIntentSucceeded::class);
});

it('rejects invalid event types', function () {
    $this->artisan('payrex:webhook-test', ['type' => 'invalid.event_type'])
        ->assertFailed()
        ->expectsOutputToContain('Invalid event type: invalid.event_type');
});

it('dispatches correct payload structure', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.succeeded'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return $event->payload['type'] === 'payment_intent.succeeded'
            && $event->payload['livemode'] === false
            && isset($event->payload['data']['resource']['id'])
            && $event->payload['data']['resource']['resource'] === 'payment_intent';
    });
});
