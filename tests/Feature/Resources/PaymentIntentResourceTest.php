<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a payment intent', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents' => Http::response(loadFixture('payment_intent/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->paymentIntents()->create([
        'amount' => 10000,
        'currency' => 'PHP',
        'payment_methods' => ['card', 'gcash'],
    ]);

    expect($result)->toBeInstanceOf(PaymentIntent::class)
        ->and($result->id)->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($result->resource)->toBe('payment_intent')
        ->and($result->amount)->toBe(10000)
        ->and($result->amountReceived)->toBe(0)
        ->and($result->amountCapturable)->toBe(0)
        ->and($result->currency)->toBe('PHP')
        ->and($result->status)->toBe(PaymentIntentStatus::AwaitingPaymentMethod)
        ->and($result->paymentMethods)->toBe(['card', 'gcash'])
        ->and($result->description)->toBe('')
        ->and($result->statementDescriptor)->toBeNull()
        ->and($result->metadata)->toBeNull()
        ->and($result->lastPaymentError)->toBeNull()
        ->and($result->latestPayment)->toBeNull()
        ->and($result->nextAction)->toBe(['type' => 'redirect', 'redirect_url' => 'https://my-application/redirect'])
        ->and($result->paymentMethodId)->toBeNull()
        ->and($result->returnUrl)->toBeNull()
        ->and($result->captureBeforeAt)->toBe(1700407880)
        ->and($result->customer)->toBeNull()
        ->and($result->clientSecret)->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX_secret_7KGizzHuLtPtaLwiRMHekBHRUo6yv52r')
        ->and($result->livemode)->toBeFalse()
        ->and($result['payment_method_options']['card']['capture_type'])->toBe('automatic');

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/payment_intents'
            && $r->method() === 'POST'
            && $r['amount'] === 10000
            && $r['currency'] === 'PHP';
    });
});

it('retrieves a payment intent', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX' => Http::response(loadFixture('payment_intent/retrieved.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->paymentIntents()->retrieve('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX');

    expect($result)->toBeInstanceOf(PaymentIntent::class)
        ->and($result->id)->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($result->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($result->amountReceived)->toBe(10000)
        ->and($result->amountCapturable)->toBe(0)
        ->and($result->latestPayment)->not->toBeNull()
        ->and($result['latest_payment']['id'])->toBe('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa')
        ->and($result->paymentMethodId)->toBe('pm_AcXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->returnUrl)->toBe('https://my-application.com/return')
        ->and($result->nextAction)->toBeNull()
        ->and($result->customer)->toBeNull();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX'
        && $r->method() === 'GET'
    );
});

it('cancels a payment intent', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX/cancel' => Http::response(loadFixture('payment_intent/cancelled.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->paymentIntents()->cancel('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX');

    expect($result)->toBeInstanceOf(PaymentIntent::class)
        ->and($result->id)->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($result->status)->toBe(PaymentIntentStatus::Canceled)
        ->and($result->amountReceived)->toBe(0)
        ->and($result->latestPayment)->toBeNull();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX/cancel'
        && $r->method() === 'POST'
    );
});

it('captures a payment intent', function () {
    Http::fake(['https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX/capture' => Http::response(loadFixture('payment_intent/captured.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->paymentIntents()->capture('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX', ['amount' => 10000]);

    expect($result)->toBeInstanceOf(PaymentIntent::class)
        ->and($result->id)->toBe('pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX')
        ->and($result->status)->toBe(PaymentIntentStatus::Succeeded)
        ->and($result->amountReceived)->toBe(10000)
        ->and($result->amountCapturable)->toBe(0)
        ->and($result->latestPayment)->not->toBeNull()
        ->and($result['latest_payment']['id'])->toBe('pay_bJdGt2XC3XNRjps3WdzjKixWfs7Zb4sa')
        ->and($result->paymentMethodId)->toBe('pm_AcXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result['payment_method_options']['card']['capture_type'])->toBe('manual');

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/payment_intents/pi_SJuGtXXC3XNRWpW3W1zQKiLWf67ZC4sX/capture'
            && $r->method() === 'POST'
            && $r['amount'] === 10000;
    });
});
