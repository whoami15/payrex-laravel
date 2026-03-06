<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('payment_intent/created.json');
    $pi = PaymentIntent::from($data);

    expect($pi->id)->toBe('pi_xxxxx')
        ->and($pi->resource)->toBe('payment_intent')
        ->and($pi->amount)->toBe(10000)
        ->and($pi->amountReceived)->toBe(0)
        ->and($pi->amountCapturable)->toBe(0)
        ->and($pi->clientSecret)->toBe('pi_xxxxx_secret_xxxxx')
        ->and($pi->currency)->toBe('PHP')
        ->and($pi->description)->toBe('')
        ->and($pi->lastPaymentError)->toBeNull()
        ->and($pi->latestPayment)->toBeNull()
        ->and($pi->nextAction)->toBe(['type' => 'redirect', 'redirect_url' => 'https://my-application/redirect'])
        ->and($pi->paymentMethodOptions)->toBe(['card' => ['capture_type' => 'automatic']])
        ->and($pi->paymentMethods)->toBe(['card', 'gcash'])
        ->and($pi->statementDescriptor)->toBeNull()
        ->and($pi->paymentMethodId)->toBeNull()
        ->and($pi->returnUrl)->toBeNull()
        ->and($pi->captureBeforeAt)->toBe(1700407880)
        ->and($pi->customer)->toBeNull()
        ->and($pi->livemode)->toBeFalse()
        ->and($pi->metadata)->toBeNull();
});

it('casts status to PaymentIntentStatus enum', function () {
    $pi = PaymentIntent::from(['id' => 'pi_1', 'resource' => 'payment_intent', 'status' => 'succeeded']);

    expect($pi->status)->toBe(PaymentIntentStatus::Succeeded);
});

it('returns null for unknown status values', function () {
    $pi = PaymentIntent::from(['id' => 'pi_1', 'resource' => 'payment_intent', 'status' => 'nonexistent_status']);

    expect($pi->status)->toBeNull();
});

it('handles missing status gracefully', function () {
    $pi = PaymentIntent::from(['id' => 'pi_1', 'resource' => 'payment_intent']);

    expect($pi->status)->toBeNull();
});

it('hydrates awaiting_capture state with amount_capturable', function () {
    $data = loadFixture('payment_intent/awaiting_capture.json');
    $pi = PaymentIntent::from($data);

    expect($pi->status)->toBe(PaymentIntentStatus::AwaitingCapture)
        ->and($pi->amount)->toBe(10000)
        ->and($pi->amountCapturable)->toBe(10000)
        ->and($pi->amountReceived)->toBe(0)
        ->and($pi->captureBeforeAt)->toBe(1701012680)
        ->and($pi->paymentMethodId)->toBe('pm_xxxxx')
        ->and($pi->paymentMethodOptions)->toBe(['card' => ['capture_type' => 'manual']]);
});

it('hydrates latestPayment as Payment when expanded', function () {
    $pi = PaymentIntent::from([
        'id' => 'pi_1',
        'resource' => 'payment_intent',
        'latest_payment' => ['id' => 'pay_1', 'resource' => 'payment', 'amount' => 5000],
    ]);

    /** @var Payment $latestPayment */
    $latestPayment = $pi->latestPayment;

    expect($latestPayment)->toBeInstanceOf(Payment::class)
        ->and($latestPayment->id)->toBe('pay_1');
});

it('hydrates latestPayment as string ID when not expanded', function () {
    $pi = PaymentIntent::from([
        'id' => 'pi_1',
        'resource' => 'payment_intent',
        'latest_payment' => 'pay_1',
    ]);

    expect($pi->latestPayment)->toBe('pay_1');
});

it('hydrates customer as Customer when expanded', function () {
    $pi = PaymentIntent::from([
        'id' => 'pi_1',
        'resource' => 'payment_intent',
        'customer' => ['id' => 'cus_1', 'resource' => 'customer', 'name' => 'Juan'],
    ]);

    /** @var Customer $customer */
    $customer = $pi->customer;

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('cus_1');
});

it('hydrates customer as string ID when not expanded', function () {
    $pi = PaymentIntent::from([
        'id' => 'pi_1',
        'resource' => 'payment_intent',
        'customer' => 'cus_1',
    ]);

    expect($pi->customer)->toBe('cus_1');
});
