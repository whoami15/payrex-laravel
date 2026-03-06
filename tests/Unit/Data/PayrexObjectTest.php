<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Data\CheckoutSession;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Data\Payout;
use LegionHQ\LaravelPayrex\Data\PayoutTransaction;
use LegionHQ\LaravelPayrex\Data\PayrexObject;
use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;

it('hydrates common properties from attributes', function () {
    $obj = new PayrexObject([
        'id' => 'obj_123',
        'resource' => 'test_object',
        'livemode' => false,
        'metadata' => ['key' => 'value'],
        'created_at' => 1700000000,
        'updated_at' => 1700000100,
    ]);

    expect($obj->id)->toBe('obj_123')
        ->and($obj->resource)->toBe('test_object')
        ->and($obj->livemode)->toBeFalse()
        ->and($obj->metadata)->toBe(['key' => 'value'])
        ->and($obj->createdAt)->toBe(1700000000)
        ->and($obj->updatedAt)->toBe(1700000100);
});

it('supports ArrayAccess for backwards compatibility', function () {
    $obj = new PayrexObject([
        'id' => 'obj_123',
        'resource' => 'test',
        'nested' => ['foo' => 'bar'],
    ]);

    expect(isset($obj['id']))->toBeTrue()
        ->and($obj['id'])->toBe('obj_123')
        ->and($obj['nested']['foo'])->toBe('bar')
        ->and(isset($obj['nonexistent']))->toBeFalse()
        ->and($obj['nonexistent'])->toBeNull();
});

it('throws LogicException on ArrayAccess mutation', function () {
    $obj = new PayrexObject(['id' => 'obj_123', 'resource' => 'test']);

    $obj['id'] = 'changed';
})->throws(LogicException::class, 'PayrexObject is immutable.');

it('throws LogicException on ArrayAccess unset', function () {
    $obj = new PayrexObject(['id' => 'obj_123', 'resource' => 'test']);

    unset($obj['id']);
})->throws(LogicException::class, 'PayrexObject is immutable.');

it('serializes to the raw attributes array', function () {
    $attributes = ['id' => 'obj_123', 'resource' => 'test', 'extra' => 'field'];
    $obj = new PayrexObject($attributes);

    expect($obj->toArray())->toBe($attributes)
        ->and(json_encode($obj))->toBe(json_encode($attributes));
});

it('creates from constructor', function () {
    $obj = new PayrexObject(['id' => 'obj_456', 'resource' => 'test']);

    expect($obj)->toBeInstanceOf(PayrexObject::class)
        ->and($obj->id)->toBe('obj_456');
});

it('throws when id is missing', function () {
    new PayrexObject(['resource' => 'test']);
})->throws(InvalidArgumentException::class, 'Missing required field: id');

it('defaults resource to null when missing', function () {
    $obj = new PayrexObject(['id' => 'obj_123']);

    expect($obj->resource)->toBeNull();
});

it('defaults livemode to null when missing', function () {
    $obj = new PayrexObject(['id' => 'obj_123', 'resource' => 'test']);

    expect($obj->livemode)->toBeNull()
        ->and($obj->metadata)->toBeNull()
        ->and($obj->createdAt)->toBeNull()
        ->and($obj->updatedAt)->toBeNull();
});

it('resolves each resource type to the correct DTO class', function (string $resourceType, string $expectedClass) {
    $obj = PayrexObject::constructFrom(['id' => 'test_123', 'resource' => $resourceType]);

    expect($obj)->toBeInstanceOf($expectedClass);
})->with([
    ['payment_intent', PaymentIntent::class],
    ['payment', Payment::class],
    ['checkout_session', CheckoutSession::class],
    ['refund', Refund::class],
    ['customer', Customer::class],
    ['billing_statement', BillingStatement::class],
    ['billing_statement_line_item', BillingStatementLineItem::class],
    ['payout', Payout::class],
    ['payout_transaction', PayoutTransaction::class],
    ['webhook', WebhookEndpoint::class],
]);

it('falls back to PayrexObject for unknown resource types', function () {
    $obj = PayrexObject::constructFrom(['id' => 'unk_123', 'resource' => 'unknown_type']);

    expect($obj)->toBeInstanceOf(PayrexObject::class)
        ->and($obj->id)->toBe('unk_123');
});

it('falls back to PayrexObject when resource key is missing', function () {
    $obj = PayrexObject::constructFrom(['id' => 'no_resource', 'resource' => '']);

    expect($obj)->toBeInstanceOf(PayrexObject::class)
        ->and($obj->id)->toBe('no_resource');
});
