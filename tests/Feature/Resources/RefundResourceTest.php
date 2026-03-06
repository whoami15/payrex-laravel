<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a refund', function () {
    Http::fake(['https://api.payrexhq.com/refunds' => Http::response(loadFixture('refund/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->refunds()->create([
        'payment_id' => 'pay_xxxxx',
        'amount' => 10000,
        'currency' => 'PHP',
        'reason' => 'others',
        'remarks' => 'The customer is disappointed about item XYZ.',
    ]);

    expect($result)->toBeInstanceOf(Refund::class)
        ->and($result->id)->toBe('re_xxxxx')
        ->and($result->resource)->toBe('refund')
        ->and($result->amount)->toBe(10000)
        ->and($result->currency)->toBe('PHP')
        ->and($result->status)->toBe(RefundStatus::Succeeded)
        ->and($result->paymentId)->toBe('pay_xxxxx')
        ->and($result->reason)->toBe(RefundReason::Others)
        ->and($result->description)->toBe('')
        ->and($result->remarks)->toBe('The customer is disappointed about item XYZ.')
        ->and($result->metadata)->toBeNull()
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/refunds'
            && $r->method() === 'POST'
            && $r['payment_id'] === 'pay_xxxxx'
            && $r['amount'] === 10000
            && $r['currency'] === 'PHP'
            && $r['reason'] === 'others';
    });
});

it('updates a refund', function () {
    Http::fake(['https://api.payrexhq.com/refunds/re_xxxxx' => Http::response(loadFixture('refund/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->refunds()->update('re_xxxxx', [
        'metadata' => ['ticket_id' => 'SUP-001', 'resolved' => 'true'],
    ]);

    expect($result)->toBeInstanceOf(Refund::class)
        ->and($result->id)->toBe('re_xxxxx')
        ->and($result->metadata)->toBe(['ticket_id' => 'SUP-001', 'resolved' => 'true']);

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/refunds/re_xxxxx'
            && $r->method() === 'PUT'
            && $r['metadata'] === ['ticket_id' => 'SUP-001', 'resolved' => 'true'];
    });
});
