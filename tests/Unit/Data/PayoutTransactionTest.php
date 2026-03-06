<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\PayoutTransaction;

it('hydrates all properties from fixture data', function () {
    $data = loadFixture('payout_transaction/list.json')['data'][0];
    $txn = PayoutTransaction::from($data);

    expect($txn->id)->toBe('po_txn_bJdGtjXC3NNRYpm3ndzzKi2WfsnZa4bs')
        ->and($txn->resource)->toBe('payout_transaction')
        ->and($txn->amount)->toBe(4569600)
        ->and($txn->netAmount)->toBe(2664200)
        ->and($txn->transactionId)->toBe('pay_aJsGbj2C34NR2pmzndnzNiSWfsn6N21S')
        ->and($txn->transactionType)->toBe('payment');
});

it('handles refund transactions with negative amounts', function () {
    $data = loadFixture('payout_transaction/list.json')['data'][1];
    $txn = PayoutTransaction::from($data);

    expect($txn->amount)->toBe(-500000)
        ->and($txn->netAmount)->toBe(-500000)
        ->and($txn->transactionType)->toBe('refund');
});

it('handles missing optional properties gracefully', function () {
    $txn = PayoutTransaction::from(['id' => 'po_txn_1', 'resource' => 'payout_transaction']);

    expect($txn->amount)->toBeNull()
        ->and($txn->netAmount)->toBeNull()
        ->and($txn->transactionId)->toBeNull()
        ->and($txn->transactionType)->toBeNull();
});
