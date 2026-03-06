<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\Payout;
use LegionHQ\LaravelPayrex\Enums\PayoutStatus;

it('hydrates all properties', function () {
    $payout = Payout::from([
        'id' => 'po_test123',
        'resource' => 'payout',
        'amount' => 500000,
        'net_amount' => 495000,
        'status' => 'successful',
        'destination' => ['bank' => 'BPI', 'account_number' => '****7890'],
        'livemode' => false,
    ]);

    expect($payout->id)->toBe('po_test123')
        ->and($payout->resource)->toBe('payout')
        ->and($payout->amount)->toBe(500000)
        ->and($payout->netAmount)->toBe(495000)
        ->and($payout->destination)->toBe(['bank' => 'BPI', 'account_number' => '****7890'])
        ->and($payout->livemode)->toBeFalse();
});

it('casts status to PayoutStatus enum', function () {
    expect((Payout::from(['id' => 'po_1', 'resource' => 'payout', 'status' => 'pending']))->status)->toBe(PayoutStatus::Pending)
        ->and((Payout::from(['id' => 'po_2', 'resource' => 'payout', 'status' => 'in_transit']))->status)->toBe(PayoutStatus::InTransit)
        ->and((Payout::from(['id' => 'po_3', 'resource' => 'payout', 'status' => 'failed']))->status)->toBe(PayoutStatus::Failed)
        ->and((Payout::from(['id' => 'po_4', 'resource' => 'payout', 'status' => 'successful']))->status)->toBe(PayoutStatus::Successful);
});

it('returns null for unknown status values', function () {
    $payout = Payout::from(['id' => 'po_1', 'resource' => 'payout', 'status' => 'nonexistent']);

    expect($payout->status)->toBeNull();
});
