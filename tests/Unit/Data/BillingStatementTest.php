<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('billing_statement/finalized.json');
    $statement = BillingStatement::from($data);

    expect($statement->id)->toBe('bstm_xxxxx')
        ->and($statement->resource)->toBe('billing_statement')
        ->and($statement->amount)->toBe(100000)
        ->and($statement->currency)->toBe('PHP')
        ->and($statement->customerId)->toBe('cus_xxxxx')
        ->and($statement->status)->toBe(BillingStatementStatus::Open)
        ->and($statement->description)->toBeNull()
        ->and($statement->billingStatementUrl)->toStartWith('https://bill.payrexhq.com/')
        ->and($statement->billingDetailsCollection)->toBe('always')
        ->and($statement->billingStatementMerchantName)->toBeNull()
        ->and($statement->billingStatementNumber)->toBe('A3EAXLGV-0001')
        ->and($statement->finalizedAt)->toBe(1721727000)
        ->and($statement->statementDescriptor)->toBeNull()
        ->and($statement->dueAt)->toBe(1721813375)
        ->and($statement->lineItems)->toHaveCount(2)
        ->and($statement->lineItems[0])->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($statement->lineItems[0]->description)->toBe('Product X')
        ->and($statement->lineItems[0]->unitPrice)->toBe(10000)
        ->and($statement->lineItems[0]->quantity)->toBe(5)
        ->and($statement->customer)->toBeInstanceOf(Customer::class)
        ->and($statement->paymentIntent)->toBeInstanceOf(PaymentIntent::class);

    /** @var Customer $customer */
    $customer = $statement->customer;

    /** @var PaymentIntent $paymentIntent */
    $paymentIntent = $statement->paymentIntent;

    expect($customer->id)->toBe('cus_xxxxx')
        ->and($paymentIntent->id)->toBe('pi_xxxxx')
        ->and($statement->paymentSettings)->toBe(['payment_methods' => ['card', 'gcash']])
        ->and($statement->livemode)->toBeFalse()
        ->and($statement->metadata)->toBeNull();
});

it('casts status to BillingStatementStatus enum', function () {
    expect((BillingStatement::from(['id' => 'bs_1', 'resource' => 'billing_statement', 'status' => 'draft']))->status)->toBe(BillingStatementStatus::Draft)
        ->and((BillingStatement::from(['id' => 'bs_2', 'resource' => 'billing_statement', 'status' => 'open']))->status)->toBe(BillingStatementStatus::Open)
        ->and((BillingStatement::from(['id' => 'bs_3', 'resource' => 'billing_statement', 'status' => 'paid']))->status)->toBe(BillingStatementStatus::Paid)
        ->and((BillingStatement::from(['id' => 'bs_4', 'resource' => 'billing_statement', 'status' => 'void']))->status)->toBe(BillingStatementStatus::Void)
        ->and((BillingStatement::from(['id' => 'bs_5', 'resource' => 'billing_statement', 'status' => 'uncollectible']))->status)->toBe(BillingStatementStatus::Uncollectible)
        ->and((BillingStatement::from(['id' => 'bs_6', 'resource' => 'billing_statement', 'status' => 'overdue']))->status)->toBe(BillingStatementStatus::Overdue);
});

it('returns null for unknown status values', function () {
    $statement = BillingStatement::from(['id' => 'bs_1', 'resource' => 'billing_statement', 'status' => 'nonexistent']);

    expect($statement->status)->toBeNull();
});

it('defaults setupFutureUsage to null when missing', function () {
    $statement = BillingStatement::from(['id' => 'bs_1', 'resource' => 'billing_statement']);

    expect($statement->setupFutureUsage)->toBeNull();
});

it('hydrates customer as string ID when not expanded', function () {
    $statement = BillingStatement::from([
        'id' => 'bs_1',
        'resource' => 'billing_statement',
        'customer' => 'cus_1',
    ]);

    expect($statement->customer)->toBe('cus_1');
});

it('hydrates paymentIntent as string ID when not expanded', function () {
    $statement = BillingStatement::from([
        'id' => 'bs_1',
        'resource' => 'billing_statement',
        'payment_intent' => 'pi_1',
    ]);

    expect($statement->paymentIntent)->toBe('pi_1');
});
