<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\PayoutTransaction;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\PayrexClient;

it('lists payout transactions', function () {
    Http::fake(['https://api.payrexhq.com/payouts/po_sdGtgXC3XNhjpsxWd5j7ijWhde7xb4sn/transactions' => Http::response(loadFixture('payout_transaction/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->payoutTransactions()->list('po_sdGtgXC3XNhjpsxWd5j7ijWhde7xb4sn');

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(PayoutTransaction::class)
        ->and($result->data[0]->id)->toBe('po_txn_bJdGtjXC3NNRYpm3ndzzKi2WfsnZa4bs')
        ->and($result->data[0]->amount)->toBe(4569600)
        ->and($result->data[0]->netAmount)->toBe(2664200)
        ->and($result->data[0]->transactionId)->toBe('pay_aJsGbj2C34NR2pmzndnzNiSWfsn6N21S')
        ->and($result->data[0]->transactionType)->toBe('payment')
        ->and($result->data[1]->id)->toBe('po_txn_cKeFukYD4OOSZqn4oeAALj3XgtoAb5ct')
        ->and($result->data[1]->amount)->toBe(-500000)
        ->and($result->data[1]->transactionType)->toBe('refund');

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/payouts/po_sdGtgXC3XNhjpsxWd5j7ijWhde7xb4sn/transactions'
        && $r->method() === 'GET'
    );
});
