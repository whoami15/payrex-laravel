<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;

it('hydrates all properties from fixture', function () {
    $data = loadFixture('billing_statement_line_item/created.json');
    $item = BillingStatementLineItem::from($data);

    expect($item->id)->toBe('bstm_li_xxxxx')
        ->and($item->resource)->toBe('billing_statement_line_item')
        ->and($item->description)->toBe('Product X')
        ->and($item->unitPrice)->toBe(10000)
        ->and($item->quantity)->toBe(5)
        ->and($item->billingStatementId)->toBe('bstm_xxxxx')
        ->and($item->livemode)->toBeFalse();
});

it('handles missing optional properties gracefully', function () {
    $item = BillingStatementLineItem::from(['id' => 'bstm_li_1', 'resource' => 'billing_statement_line_item']);

    expect($item->description)->toBeNull()
        ->and($item->unitPrice)->toBeNull()
        ->and($item->quantity)->toBeNull()
        ->and($item->billingStatementId)->toBeNull();
});
