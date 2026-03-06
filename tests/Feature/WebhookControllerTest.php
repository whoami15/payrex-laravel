<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use LegionHQ\LaravelPayrex\Events\BillingStatementCreated;
use LegionHQ\LaravelPayrex\Events\BillingStatementDeleted;
use LegionHQ\LaravelPayrex\Events\BillingStatementFinalized;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemCreated;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemDeleted;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemUpdated;
use LegionHQ\LaravelPayrex\Events\BillingStatementMarkedUncollectible;
use LegionHQ\LaravelPayrex\Events\BillingStatementOverdue;
use LegionHQ\LaravelPayrex\Events\BillingStatementPaid;
use LegionHQ\LaravelPayrex\Events\BillingStatementSent;
use LegionHQ\LaravelPayrex\Events\BillingStatementUpdated;
use LegionHQ\LaravelPayrex\Events\BillingStatementVoided;
use LegionHQ\LaravelPayrex\Events\BillingStatementWillBeDue;
use LegionHQ\LaravelPayrex\Events\CheckoutSessionExpired;
use LegionHQ\LaravelPayrex\Events\PaymentIntentAwaitingCapture;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\PayoutDeposited;
use LegionHQ\LaravelPayrex\Events\RefundCreated;
use LegionHQ\LaravelPayrex\Events\RefundUpdated;
use LegionHQ\LaravelPayrex\Events\WebhookReceived;
use LegionHQ\LaravelPayrex\Tests\TestCase;

function sendSignedWebhook(TestCase $test, array $payload): TestResponse
{
    $json = json_encode($payload);
    $timestamp = time();
    $secret = config('payrex.webhook.secret');
    $signature = hash_hmac('sha256', $timestamp.'.'.$json, $secret);

    return $test->call(
        'POST',
        route('payrex.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYREX_SIGNATURE' => "t={$timestamp},te={$signature},li=",
        ],
        $json
    );
}

it('rejects requests without a signature header', function () {
    $response = $this->postJson(route('payrex.webhook'), [
        'type' => 'payment_intent.succeeded',
    ]);

    $response->assertStatus(403);
});

it('rejects requests with an invalid signature', function () {
    $response = $this->postJson(
        route('payrex.webhook'),
        ['type' => 'payment_intent.succeeded'],
        ['Payrex-Signature' => 't=123,te=invalid,li=']
    );

    $response->assertStatus(403);
});

it('accepts requests with a valid signature and dispatches events', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_123',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'pi_123']],
    ];

    $response = sendSignedWebhook($this, $payload);

    $response->assertStatus(200);

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(PaymentIntentSucceeded::class);
});

it('dispatches correct event class for each event type', function (string $type, string $class) {
    Event::fake();

    $payload = [
        'id' => 'evt_test',
        'type' => $type,
        'livemode' => false,
        'data' => ['resource' => ['id' => 'res_123']],
    ];

    $response = sendSignedWebhook($this, $payload);

    $response->assertStatus(200);
    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched($class);
})->with([
    ['payment_intent.succeeded', PaymentIntentSucceeded::class],
    ['payment_intent.awaiting_capture', PaymentIntentAwaitingCapture::class],
    ['checkout_session.expired', CheckoutSessionExpired::class],
    ['payout.deposited', PayoutDeposited::class],
    ['refund.created', RefundCreated::class],
    ['refund.updated', RefundUpdated::class],
    ['billing_statement.created', BillingStatementCreated::class],
    ['billing_statement.updated', BillingStatementUpdated::class],
    ['billing_statement.deleted', BillingStatementDeleted::class],
    ['billing_statement.finalized', BillingStatementFinalized::class],
    ['billing_statement.sent', BillingStatementSent::class],
    ['billing_statement.marked_uncollectible', BillingStatementMarkedUncollectible::class],
    ['billing_statement.voided', BillingStatementVoided::class],
    ['billing_statement.paid', BillingStatementPaid::class],
    ['billing_statement.will_be_due', BillingStatementWillBeDue::class],
    ['billing_statement.overdue', BillingStatementOverdue::class],
    ['billing_statement_line_item.created', BillingStatementLineItemCreated::class],
    ['billing_statement_line_item.updated', BillingStatementLineItemUpdated::class],
    ['billing_statement_line_item.deleted', BillingStatementLineItemDeleted::class],
]);

it('handles unknown event types gracefully', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_test',
        'type' => 'unknown.event_type',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'res_123']],
    ];

    $response = sendSignedWebhook($this, $payload);

    $response->assertStatus(200);
    Event::assertDispatched(WebhookReceived::class);
});

it('dispatches event with correct payload data', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_payload_test',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'pi_payload_123', 'resource' => 'payment_intent', 'amount' => 50000]],
    ];

    $response = sendSignedWebhook($this, $payload);

    $response->assertStatus(200);

    Event::assertDispatched(PaymentIntentSucceeded::class, function ($event) {
        return $event->payload['id'] === 'evt_payload_test'
            && $event->data()->id === 'pi_payload_123'
            && $event->data()->amount === 50000;
    });
});

it('dispatches event with live mode signature', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_live',
        'type' => 'payment_intent.succeeded',
        'livemode' => true,
        'data' => ['resource' => ['id' => 'pi_live_123']],
    ];

    $json = json_encode($payload);
    $timestamp = time();
    $secret = config('payrex.webhook.secret');
    $signature = hash_hmac('sha256', $timestamp.'.'.$json, $secret);

    $response = $this->call(
        'POST',
        route('payrex.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYREX_SIGNATURE' => "t={$timestamp},te=,li={$signature}",
        ],
        $json
    );

    $response->assertStatus(200);
    Event::assertDispatched(PaymentIntentSucceeded::class);
});

it('rejects malformed JSON payload', function () {
    $timestamp = time();
    $secret = config('payrex.webhook.secret');
    $body = 'not-valid-json';
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

    $response = $this->call(
        'POST',
        route('payrex.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYREX_SIGNATURE' => "t={$timestamp},te={$signature},li=",
        ],
        $body
    );

    $response->assertStatus(400);
    $response->assertSee('Invalid JSON payload');
});

it('returns 200 with webhook handled message', function () {
    Event::fake();

    $payload = [
        'id' => 'evt_msg',
        'type' => 'payment_intent.succeeded',
        'livemode' => false,
        'data' => ['resource' => ['id' => 'pi_123']],
    ];

    $response = sendSignedWebhook($this, $payload);

    $response->assertStatus(200);
    $response->assertSee('Webhook handled');
});
