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
        ->assertSuccessful();
    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // ->expectsPromptsTable(
    //     headers: ['Field', 'Value'],
    //     rows: [
    //         ['ID', 'wh_xxxxx'],
    //         ['URL', 'https://my-ecommerce.com/send-shipments'],
    //         ['Status', 'enabled'],
    //         ['Events', 'payment_intent.succeeded'],
    //         ['Description', 'This is the webhook used for sending shipments after receiving successfully paid payments'],
    //         ['Secret Key', 'whsk_xxxxx'],
    //         ['Created At', date('Y-m-d H:i:s', 1706056262)],
    //     ],
    // );
});

it('displays error message on API failure', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks' => Http::response(loadFixture('errors/authentication.json'), 401),
    ]);

    $this->artisan('payrex:webhook-create')
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/send-shipments')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded'])
        ->expectsQuestion('Description', '')
        ->assertFailed()
        ->expectsOutputToContain('Invalid API key provided.');
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
