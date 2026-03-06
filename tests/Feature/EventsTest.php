<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
use LegionHQ\LaravelPayrex\Events\WebhookReceived;

it('dispatches both generic and typed events via dispatchWebhook', function () {
    Event::fake();

    PayrexEvent::dispatchWebhook([
        'type' => 'payment_intent.succeeded',
        'data' => ['resource' => ['id' => 'pi_123']],
    ]);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(PaymentIntentSucceeded::class);
});

it('dispatches only generic event for unknown types via dispatchWebhook', function () {
    Event::fake();

    PayrexEvent::dispatchWebhook([
        'type' => 'unknown.event',
        'data' => ['resource' => ['id' => 'res_123']],
    ]);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertNotDispatched(PaymentIntentSucceeded::class);
});

it('dispatches only generic event when type is missing via dispatchWebhook', function () {
    Event::fake();

    PayrexEvent::dispatchWebhook(['data' => ['resource' => ['id' => 'res_123']]]);

    Event::assertDispatched(WebhookReceived::class);
});
