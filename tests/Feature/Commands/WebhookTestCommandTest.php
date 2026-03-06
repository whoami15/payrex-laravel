<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemCreated;
use LegionHQ\LaravelPayrex\Events\BillingStatementPaid;
use LegionHQ\LaravelPayrex\Events\CashBalanceFundsAvailable;
use LegionHQ\LaravelPayrex\Events\CheckoutSessionExpired;
use LegionHQ\LaravelPayrex\Events\PaymentIntentAmountCapturable;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\PayoutDeposited;
use LegionHQ\LaravelPayrex\Events\RefundCreated;
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

it('generates correct ID prefix for payment intent events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.succeeded'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return str_starts_with($event->payload['id'], 'evt_test_')
            && str_starts_with($event->payload['data']['id'], 'pi_test_')
            && $event->payload['data']['resource'] === 'payment_intent';
    });
});

it('includes payment intent specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.succeeded'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        $data = $event->payload['data'];

        return $data['amount'] === 10000
            && $data['amount_received'] === 10000
            && $data['amount_capturable'] === 0
            && $data['currency'] === 'PHP'
            && $data['status'] === 'succeeded'
            && array_key_exists('payment_methods', $data)
            && array_key_exists('latest_payment', $data)
            && array_key_exists('client_secret', $data);
    });
});

it('sets amount_capturable for manual capture events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.amount_capturable'])
        ->assertSuccessful();

    Event::assertDispatched(PaymentIntentAmountCapturable::class, function ($event) {
        return $event->payload['data']['amount_capturable'] === 10000
            && $event->payload['data']['amount_received'] === 0;
    });
});

it('includes refund specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'refund.created'])
        ->assertSuccessful();

    Event::assertDispatched(RefundCreated::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 're_test_')
            && $data['reason'] === 'requested_by_customer'
            && array_key_exists('payment_id', $data)
            && str_starts_with($data['payment_id'], 'pay_test_');
    });
});

it('includes billing statement specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement.paid'])
        ->assertSuccessful();

    Event::assertDispatched(BillingStatementPaid::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 'bstm_test_')
            && $data['status'] === 'paid'
            && array_key_exists('customer_id', $data)
            && array_key_exists('billing_statement_url', $data)
            && array_key_exists('billing_statement_number', $data)
            && array_key_exists('line_items', $data)
            && array_key_exists('customer', $data)
            && is_array($data['customer'])
            && is_array($data['payment_intent'])
            && $data['payment_intent']['status'] === 'succeeded';
    });
});

it('includes billing statement line item specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement_line_item.created'])
        ->assertSuccessful();

    Event::assertDispatched(BillingStatementLineItemCreated::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 'bstm_li_test_')
            && $data['description'] === 'Test Line Item'
            && $data['quantity'] === 1
            && $data['unit_price'] === 50000
            && array_key_exists('billing_statement_id', $data)
            && str_starts_with($data['billing_statement_id'], 'bstm_test_');
    });
});

it('sets draft status for billing statement created events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement.created'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        $data = $event->payload['data'];

        return $data['status'] === 'draft'
            && $data['finalized_at'] === 0
            && $data['payment_intent'] === null;
    });
});

it('sets open status for billing statement finalized events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement.finalized'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        $data = $event->payload['data'];

        return $data['status'] === 'open'
            && $data['finalized_at'] > 0
            && is_array($data['payment_intent']);
    });
});

it('sets void status for billing statement voided events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement.voided'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return $event->payload['data']['status'] === 'void';
    });
});

it('sets uncollectible status for billing statement marked_uncollectible events', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'billing_statement.marked_uncollectible'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return $event->payload['data']['status'] === 'uncollectible';
    });
});

it('includes checkout session specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'checkout_session.expired'])
        ->assertSuccessful();

    Event::assertDispatched(CheckoutSessionExpired::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 'cs_test_')
            && $data['status'] === 'expired'
            && $data['currency'] === 'PHP'
            && array_key_exists('line_items', $data)
            && array_key_exists('url', $data)
            && array_key_exists('success_url', $data)
            && array_key_exists('cancel_url', $data)
            && array_key_exists('payment_methods', $data)
            && $data['submit_type'] === 'pay';
    });
});

it('includes payout specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payout.deposited'])
        ->assertSuccessful();

    Event::assertDispatched(PayoutDeposited::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 'po_test_')
            && $data['resource'] === 'payout'
            && $data['amount'] === 100000
            && $data['currency'] === 'PHP'
            && $data['status'] === 'deposited';
    });
});

it('includes cash balance specific fields', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'cash_balance.funds_available'])
        ->assertSuccessful();

    Event::assertDispatched(CashBalanceFundsAvailable::class, function ($event) {
        $data = $event->payload['data'];

        return str_starts_with($data['id'], 'cb_test_')
            && $data['resource'] === 'cash_balance'
            && $data['amount'] === 100000
            && $data['status'] === 'funds_available';
    });
});

it('includes previous_attributes in payload', function () {
    Event::fake();

    $this->artisan('payrex:webhook-test', ['type' => 'payment_intent.succeeded'])
        ->assertSuccessful();

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return array_key_exists('previous_attributes', $event->payload)
            && is_array($event->payload['previous_attributes']);
    });
});
