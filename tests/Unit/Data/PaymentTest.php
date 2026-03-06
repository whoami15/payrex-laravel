<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Enums\PaymentStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('payment/retrieved.json');
    $payment = Payment::from($data);

    expect($payment->id)->toBe('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa')
        ->and($payment->resource)->toBe('payment')
        ->and($payment->amount)->toBe(4569600)
        ->and($payment->amountRefunded)->toBe(0)
        ->and($payment->billing)->toBe([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@email.com',
            'phone' => null,
            'address' => [
                'line1' => '123453',
                'line2' => null,
                'city' => 'Pasay',
                'state' => 'Metro Manila',
                'postal_code' => '1829',
                'country' => 'PH',
            ],
        ])
        ->and($payment->currency)->toBe('PHP')
        ->and($payment->description)->toBeNull()
        ->and($payment->fee)->toBe(2500)
        ->and($payment->netAmount)->toBe(4549257)
        ->and($payment->paymentIntentId)->toBe('pi_nzxCsMb2FQ4WitBZAaQgw3CSTJBnW8id')
        ->and($payment->paymentMethod)->toBe(['type' => 'card', 'card' => ['first6' => '511111', 'last4' => '1111', 'brand' => 'visa']])
        ->and($payment->customer)->toBeNull()
        ->and($payment->pageSession)->toBeNull()
        ->and($payment->refunded)->toBeFalse()
        ->and($payment->livemode)->toBeFalse()
        ->and($payment->metadata)->toBeNull();
});

it('casts status to PaymentStatus enum', function () {
    $payment = Payment::from(['id' => 'pay_1', 'resource' => 'payment', 'status' => 'paid']);

    expect($payment->status)->toBe(PaymentStatus::Paid);
});

it('casts failed status to PaymentStatus enum', function () {
    $payment = Payment::from(['id' => 'pay_1', 'resource' => 'payment', 'status' => 'failed']);

    expect($payment->status)->toBe(PaymentStatus::Failed);
});

it('returns null for unknown status values', function () {
    $payment = Payment::from(['id' => 'pay_1', 'resource' => 'payment', 'status' => 'nonexistent_status']);

    expect($payment->status)->toBeNull();
});

it('hydrates customer as Customer when expanded', function () {
    $payment = Payment::from([
        'id' => 'pay_1',
        'resource' => 'payment',
        'customer' => ['id' => 'cus_1', 'resource' => 'customer', 'name' => 'Juan'],
    ]);

    /** @var Customer $customer */
    $customer = $payment->customer;

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('cus_1');
});

it('hydrates customer as string ID when not expanded', function () {
    $payment = Payment::from([
        'id' => 'pay_1',
        'resource' => 'payment',
        'customer' => 'cus_1',
    ]);

    expect($payment->customer)->toBe('cus_1');
});
