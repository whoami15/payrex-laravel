<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a billing statement line item', function () {
    Http::fake(['https://api.payrexhq.com/billing_statement_line_items' => Http::response(loadFixture('billing_statement_line_item/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatementLineItems()->create([
        'billing_statement_id' => 'bstm_xxxxx',
        'description' => 'Product X',
        'unit_price' => 10000,
        'quantity' => 5,
    ]);

    expect($result)->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($result->id)->toBe('bstm_li_xxxxx')
        ->and($result->resource)->toBe('billing_statement_line_item')
        ->and($result->description)->toBe('Product X')
        ->and($result->unitPrice)->toBe(10000)
        ->and($result->quantity)->toBe(5)
        ->and($result->billingStatementId)->toBe('bstm_xxxxx')
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/billing_statement_line_items'
            && $r->method() === 'POST'
            && $r['billing_statement_id'] === 'bstm_xxxxx'
            && $r['description'] === 'Product X'
            && $r['unit_price'] === 10000
            && $r['quantity'] === 5;
    });
});

it('updates a billing statement line item', function () {
    Http::fake(['https://api.payrexhq.com/billing_statement_line_items/bstm_li_xxxxx' => Http::response(loadFixture('billing_statement_line_item/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatementLineItems()->update('bstm_li_xxxxx', [
        'description' => 'Product X (Updated)',
        'unit_price' => 12000,
        'quantity' => 3,
    ]);

    expect($result)->toBeInstanceOf(BillingStatementLineItem::class)
        ->and($result->id)->toBe('bstm_li_xxxxx')
        ->and($result->description)->toBe('Product X (Updated)')
        ->and($result->unitPrice)->toBe(12000)
        ->and($result->quantity)->toBe(3);

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/billing_statement_line_items/bstm_li_xxxxx'
            && $r->method() === 'PUT'
            && $r['description'] === 'Product X (Updated)'
            && $r['unit_price'] === 12000
            && $r['quantity'] === 3;
    });
});

it('deletes a billing statement line item', function () {
    Http::fake(['https://api.payrexhq.com/billing_statement_line_items/bstm_li_xxxxx' => Http::response(loadFixture('billing_statement_line_item/deleted.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatementLineItems()->delete('bstm_li_xxxxx');

    expect($result)->toBeInstanceOf(DeletedResource::class)
        ->and($result->id)->toBe('bstm_li_xxxxx')
        ->and($result->deleted)->toBeTrue();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statement_line_items/bstm_li_xxxxx'
        && $r->method() === 'DELETE'
    );
});
