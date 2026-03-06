<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('refund/created.json');
    $refund = Refund::from($data);

    expect($refund->id)->toBe('re_xxxxx')
        ->and($refund->resource)->toBe('refund')
        ->and($refund->amount)->toBe(10000)
        ->and($refund->currency)->toBe('PHP')
        ->and($refund->description)->toBe('')
        ->and($refund->remarks)->toBe('The customer is disappointed about item XYZ.')
        ->and($refund->paymentId)->toBe('pay_xxxxx')
        ->and($refund->livemode)->toBeFalse()
        ->and($refund->metadata)->toBeNull();
});

it('casts status to RefundStatus enum', function () {
    $refund = Refund::from(['id' => 're_1', 'resource' => 'refund', 'status' => 'succeeded']);
    expect($refund->status)->toBe(RefundStatus::Succeeded);

    $refund = Refund::from(['id' => 're_2', 'resource' => 'refund', 'status' => 'pending']);
    expect($refund->status)->toBe(RefundStatus::Pending);

    $refund = Refund::from(['id' => 're_3', 'resource' => 'refund', 'status' => 'failed']);
    expect($refund->status)->toBe(RefundStatus::Failed);
});

it('casts reason to RefundReason enum', function () {
    $refund = Refund::from(['id' => 're_1', 'resource' => 'refund', 'reason' => 'others']);
    expect($refund->reason)->toBe(RefundReason::Others);

    $refund = Refund::from(['id' => 're_2', 'resource' => 'refund', 'reason' => 'requested_by_customer']);
    expect($refund->reason)->toBe(RefundReason::RequestedByCustomer);

    $refund = Refund::from(['id' => 're_3', 'resource' => 'refund', 'reason' => 'fraudulent']);
    expect($refund->reason)->toBe(RefundReason::Fraudulent);
});

it('returns null for unknown enum values', function () {
    $refund = Refund::from(['id' => 're_1', 'resource' => 'refund', 'status' => 'unknown', 'reason' => 'unknown']);

    expect($refund->status)->toBeNull()
        ->and($refund->reason)->toBeNull();
});
