<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\PayoutTransaction;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Enums\PayoutTransactionType;
use LegionHQ\LaravelPayrex\PayrexClient;

it('lists payout transactions', function () {
    Http::fake(['https://api.payrexhq.com/payouts/po_xxxxx/transactions' => Http::response(loadFixture('payout_transaction/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->payoutTransactions()->list('po_xxxxx');

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(3)
        ->and($result->data[0])->toBeInstanceOf(PayoutTransaction::class)
        ->and($result->data[0]->id)->toBe('po_txn_xxxxx')
        ->and($result->data[0]->amount)->toBe(4569600)
        ->and($result->data[0]->netAmount)->toBe(2664200)
        ->and($result->data[0]->transactionId)->toBe('pay_xxxxx')
        ->and($result->data[0]->transactionType)->toBe(PayoutTransactionType::Payment)
        ->and($result->data[1]->id)->toBe('po_txn_yyyyy')
        ->and($result->data[1]->amount)->toBe(-500000)
        ->and($result->data[1]->transactionType)->toBe(PayoutTransactionType::Refund)
        ->and($result->data[2]->id)->toBe('po_txn_zzzzz')
        ->and($result->data[2]->amount)->toBe(-25000)
        ->and($result->data[2]->transactionType)->toBe(PayoutTransactionType::Adjustment);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/payouts/po_xxxxx/transactions'
        && $r->method() === 'GET'
    );
});
