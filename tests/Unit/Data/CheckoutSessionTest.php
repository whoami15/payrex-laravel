<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\CheckoutSession;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\CheckoutSessionStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('checkout_session/created.json');
    $session = CheckoutSession::from($data);

    expect($session->id)->toBe('cs_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($session->resource)->toBe('checkout_session')
        ->and($session->amount)->toBe(100000)
        ->and($session->clientSecret)->toBe('cs_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS_secret_tttefYgf9BgAnuiq9bN8EuwrpUShZU4E')
        ->and($session->currency)->toBe('PHP')
        ->and($session->customerId)->toBeNull()
        ->and($session->customer)->toBeNull()
        ->and($session->customerReferenceId)->toBeNull()
        ->and($session->description)->toBe('Some description')
        ->and($session->url)->toBe('https://checkout.payrexhq.com/c/cs_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS_secret_tttefYgf9BgAnuiq9bN8EuwrpUShZU4E')
        ->and($session->lineItems)->toHaveCount(2)
        ->and($session->successUrl)->toBe('http://some-url.com')
        ->and($session->cancelUrl)->toBe('http://some-url.com')
        ->and($session->paymentIntent)->toBeInstanceOf(PaymentIntent::class);

    /** @var PaymentIntent $paymentIntent */
    $paymentIntent = $session->paymentIntent;

    expect($paymentIntent->id)->toBe('pi_UDQ5s2yLAeE4h1CJsP9Mm6RYYk7MMnsb')
        ->and($session->paymentMethods)->toBe(['card', 'gcash'])
        ->and($session->paymentMethodOptions)->toBe(['card' => ['capture_type' => 'automatic']])
        ->and($session->billingDetailsCollection)->toBe('always')
        ->and($session->submitType)->toBe('pay')
        ->and($session->statementDescriptor)->toBe('Override statement descriptor')
        ->and($session->expiresAt)->toBe(1721813375)
        ->and($session->livemode)->toBeFalse()
        ->and($session->metadata)->toBeNull();
});

it('casts status to CheckoutSessionStatus enum', function () {
    $session = CheckoutSession::from(['id' => 'cs_1', 'resource' => 'checkout_session', 'status' => 'active']);
    expect($session->status)->toBe(CheckoutSessionStatus::Active);

    $session = CheckoutSession::from(['id' => 'cs_2', 'resource' => 'checkout_session', 'status' => 'completed']);
    expect($session->status)->toBe(CheckoutSessionStatus::Completed);

    $session = CheckoutSession::from(['id' => 'cs_3', 'resource' => 'checkout_session', 'status' => 'expired']);
    expect($session->status)->toBe(CheckoutSessionStatus::Expired);
});

it('returns null for unknown status values', function () {
    $session = CheckoutSession::from(['id' => 'cs_1', 'resource' => 'checkout_session', 'status' => 'nonexistent']);

    expect($session->status)->toBeNull();
});

it('hydrates paymentIntent as string ID when not expanded', function () {
    $session = CheckoutSession::from([
        'id' => 'cs_1',
        'resource' => 'checkout_session',
        'payment_intent' => 'pi_1',
    ]);

    expect($session->paymentIntent)->toBe('pi_1');
});

it('hydrates customer as expanded Customer object', function () {
    $session = CheckoutSession::from([
        'id' => 'cs_1',
        'resource' => 'checkout_session',
        'customer_id' => 'cus_xxxxx',
        'customer' => [
            'id' => 'cus_xxxxx',
            'resource' => 'customer',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ],
    ]);

    expect($session->customerId)->toBe('cus_xxxxx')
        ->and($session->customer)->toBeInstanceOf(Customer::class)
        ->and($session->customer->name)->toBe('Test Customer');
});

it('hydrates customer as string ID when not expanded', function () {
    $session = CheckoutSession::from([
        'id' => 'cs_1',
        'resource' => 'checkout_session',
        'customer_id' => 'cus_xxxxx',
        'customer' => 'cus_xxxxx',
    ]);

    expect($session->customerId)->toBe('cus_xxxxx')
        ->and($session->customer)->toBe('cus_xxxxx');
});
