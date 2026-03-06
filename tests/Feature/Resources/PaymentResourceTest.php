<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Enums\PaymentStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('retrieves a payment', function () {
    Http::fake(['https://api.payrexhq.com/payments/pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa' => Http::response(loadFixture('payment/retrieved.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->payments()->retrieve('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa');

    expect($result)->toBeInstanceOf(Payment::class)
        ->and($result->id)->toBe('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa')
        ->and($result->resource)->toBe('payment')
        ->and($result->amount)->toBe(4569600)
        ->and($result->amountRefunded)->toBe(0)
        ->and($result->currency)->toBe('PHP')
        ->and($result->description)->toBeNull()
        ->and($result->fee)->toBe(2500)
        ->and($result->netAmount)->toBe(4549257)
        ->and($result->paymentIntentId)->toBe('pi_nzxCsMb2FQ4WitBZAaQgw3CSTJBnW8id')
        ->and($result->status)->toBe(PaymentStatus::Paid)
        ->and($result->refunded)->toBeFalse()
        ->and($result->customer)->toBeNull()
        ->and($result->pageSession)->toBeNull()
        ->and($result->metadata)->toBeNull()
        ->and($result['billing']['name'])->toBe('Juan Dela Cruz')
        ->and($result['billing']['email'])->toBe('juan@email.com')
        ->and($result['billing']['phone'])->toBeNull()
        ->and($result['billing']['address']['city'])->toBe('Pasay')
        ->and($result['payment_method']['type'])->toBe('card')
        ->and($result['payment_method']['card']['first6'])->toBe('511111')
        ->and($result['payment_method']['card']['last4'])->toBe('1111')
        ->and($result['payment_method']['card']['brand'])->toBe('visa')
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/payments/pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa'
        && $r->method() === 'GET'
    );
});

it('updates a payment', function () {
    Http::fake(['https://api.payrexhq.com/payments/pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa' => Http::response(loadFixture('payment/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->payments()->update('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa', [
        'description' => 'Updated payment description',
        'metadata' => ['order_id' => 'ORD-001', 'updated' => 'true'],
    ]);

    expect($result)->toBeInstanceOf(Payment::class)
        ->and($result->id)->toBe('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa')
        ->and($result->description)->toBe('Updated payment description')
        ->and($result->metadata)->toBe(['order_id' => 'ORD-001', 'updated' => 'true']);

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/payments/pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa'
            && $r->method() === 'PUT'
            && $r['description'] === 'Updated payment description';
    });
});
