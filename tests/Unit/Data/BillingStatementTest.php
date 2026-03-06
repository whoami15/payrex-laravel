<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('billing_statement/created.json');
    $statement = BillingStatement::from($data);

    expect($statement->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($statement->resource)->toBe('billing_statement')
        ->and($statement->amount)->toBe(100000)
        ->and($statement->currency)->toBe('PHP')
        ->and($statement->customerId)->toBe('cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22')
        ->and($statement->description)->toBeNull()
        ->and($statement->url)->toStartWith('https://bill.payrexhq.com/')
        ->and($statement->billingDetailsCollection)->toBe('always')
        ->and($statement->dueAt)->toBe(1721813375)
        ->and($statement->lineItems)->toHaveCount(2)
        ->and($statement->customer)->toBeInstanceOf(Customer::class)
        ->and($statement->customer->id)->toBe('cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22')
        ->and($statement->paymentIntent)->toBeInstanceOf(PaymentIntent::class)
        ->and($statement->paymentIntent->id)->toBe('pi_UDQ5s2yLAeE4h1CJsP9Mm6RYYk7MMnsb')
        ->and($statement->paymentSettings)->toBe(['payment_methods' => ['card', 'gcash']])
        ->and($statement->livemode)->toBeFalse()
        ->and($statement->metadata)->toBeNull();
});

it('casts status to BillingStatementStatus enum', function () {
    expect((BillingStatement::from(['id' => 'bs_1', 'resource' => 'billing_statement', 'status' => 'draft']))->status)->toBe(BillingStatementStatus::Draft)
        ->and((BillingStatement::from(['id' => 'bs_2', 'resource' => 'billing_statement', 'status' => 'open']))->status)->toBe(BillingStatementStatus::Open)
        ->and((BillingStatement::from(['id' => 'bs_3', 'resource' => 'billing_statement', 'status' => 'paid']))->status)->toBe(BillingStatementStatus::Paid)
        ->and((BillingStatement::from(['id' => 'bs_4', 'resource' => 'billing_statement', 'status' => 'void']))->status)->toBe(BillingStatementStatus::Void)
        ->and((BillingStatement::from(['id' => 'bs_5', 'resource' => 'billing_statement', 'status' => 'uncollectible']))->status)->toBe(BillingStatementStatus::Uncollectible);
});

it('returns null for unknown status values', function () {
    $statement = BillingStatement::from(['id' => 'bs_1', 'resource' => 'billing_statement', 'status' => 'nonexistent']);

    expect($statement->status)->toBeNull();
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
