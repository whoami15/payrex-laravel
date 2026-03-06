<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('creates a webhook endpoint', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/created.json')),
    ]);

    $this->artisan('payrex:webhook-create')
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/send-shipments')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded'])
        ->expectsQuestion('Description', 'This is the webhook used for sending shipments after receiving successfully paid payments')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint created successfully.');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks'
        && $request->method() === 'POST'
    );
});

it('creates a webhook endpoint without description', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/created.json')),
    ]);

    $this->artisan('payrex:webhook-create')
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/send-shipments')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded'])
        ->expectsQuestion('Description', '')
        ->assertSuccessful();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks'
        && $request->method() === 'POST'
    );
});

it('displays the secret key after creation', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/created.json')),
    ]);

    $this->artisan('payrex:webhook-create')
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/send-shipments')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded'])
        ->expectsQuestion('Description', '')
        ->assertSuccessful()
        ->expectsOutputToContain('whsk_cU8kMThbLEkF3yvz1ygCrPrBdAWguuCU');
});

it('handles unknown webhook status gracefully', function () {
    $fixture = loadFixture('webhook/created.json');
    $fixture['status'] = 'unknown_status';

    Http::fake([
        'https://api.payrexhq.com/webhooks' => Http::response($fixture),
    ]);

    $this->artisan('payrex:webhook-create')
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/send-shipments')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded'])
        ->expectsQuestion('Description', '')
        ->assertSuccessful();
});
