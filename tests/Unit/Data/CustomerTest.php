<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Customer;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('customer/created.json');
    $customer = Customer::from($data);

    expect($customer->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($customer->resource)->toBe('customer')
        ->and($customer->name)->toBe('Juan Dela Cruz')
        ->and($customer->email)->toBe('juan@gmail.com')
        ->and($customer->currency)->toBe('PHP')
        ->and($customer->billingStatementPrefix)->toBe('PKYG9MA2')
        ->and($customer->nextBillingStatementSequenceNumber)->toBe('1')
        ->and($customer->billing)->toBeNull()
        ->and($customer->livemode)->toBeFalse()
        ->and($customer->metadata)->toBeNull();
});

it('handles missing optional properties gracefully', function () {
    $customer = Customer::from(['id' => 'cus_1', 'resource' => 'customer']);

    expect($customer->name)->toBeNull()
        ->and($customer->email)->toBeNull()
        ->and($customer->currency)->toBeNull()
        ->and($customer->billingStatementPrefix)->toBeNull()
        ->and($customer->billing)->toBeNull();
});
