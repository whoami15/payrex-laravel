<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\PayoutTransaction;
use LegionHQ\LaravelPayrex\Enums\PayoutTransactionType;

it('hydrates all properties from fixture data', function () {
    $data = loadFixture('payout_transaction/list.json')['data'][0];
    $txn = PayoutTransaction::from($data);

    expect($txn->id)->toBe('po_txn_xxxxx')
        ->and($txn->resource)->toBe('payout_transaction')
        ->and($txn->amount)->toBe(4569600)
        ->and($txn->netAmount)->toBe(2664200)
        ->and($txn->transactionId)->toBe('pay_xxxxx')
        ->and($txn->transactionType)->toBe(PayoutTransactionType::Payment);
});

it('handles refund transactions with negative amounts', function () {
    $data = loadFixture('payout_transaction/list.json')['data'][1];
    $txn = PayoutTransaction::from($data);

    expect($txn->amount)->toBe(-500000)
        ->and($txn->netAmount)->toBe(-500000)
        ->and($txn->transactionType)->toBe(PayoutTransactionType::Refund);
});

it('handles adjustment transactions', function () {
    $data = loadFixture('payout_transaction/list.json')['data'][2];
    $txn = PayoutTransaction::from($data);

    expect($txn->amount)->toBe(-25000)
        ->and($txn->netAmount)->toBe(-25000)
        ->and($txn->transactionId)->toBeNull()
        ->and($txn->transactionType)->toBe(PayoutTransactionType::Adjustment);
});

it('handles missing optional properties gracefully', function () {
    $txn = PayoutTransaction::from(['id' => 'po_txn_1', 'resource' => 'payout_transaction']);

    expect($txn->amount)->toBeNull()
        ->and($txn->netAmount)->toBeNull()
        ->and($txn->transactionId)->toBeNull()
        ->and($txn->transactionType)->toBeNull();
});

it('returns null for unknown transaction type values', function () {
    $txn = PayoutTransaction::from([
        'id' => 'po_txn_1',
        'resource' => 'payout_transaction',
        'transaction_type' => 'nonexistent',
    ]);

    expect($txn->transactionType)->toBeNull();
});
