<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('updates a webhook endpoint', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_xxxxx' => Http::sequence()
            ->push(loadFixture('webhook/created.json'))
            ->push(loadFixture('webhook/updated.json')),
    ]);

    $this->artisan('payrex:webhook-update', ['id' => 'wh_xxxxx'])
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/webhook-updated')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded', 'refund.created'])
        ->expectsQuestion('Description', 'Updated webhook endpoint')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint updated successfully.');
    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // ->expectsPromptsTable(
    //     headers: ['Field', 'Value'],
    //     rows: [
    //         ['ID', 'wh_xxxxx'],
    //         ['URL', 'https://my-ecommerce.com/webhook-updated'],
    //         ['Status', 'enabled'],
    //         ['Events', "payment_intent.succeeded\nrefund.created"],
    //         ['Description', 'Updated webhook endpoint'],
    //         ['Created At', date('Y-m-d H:i:s', 1706056262)],
    //     ],
    // );

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_xxxxx'
        && $request->method() === 'PUT'
    );
});

it('displays error message when webhook not found', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_nonexistent' => Http::response(loadFixture('errors/resource_not_found.json'), 404),
    ]);

    $this->artisan('payrex:webhook-update', ['id' => 'wh_nonexistent'])
        ->assertFailed()
        ->expectsOutputToContain('The resource with ID pi_nonexistent was not found.');
});

it('handles unknown webhook status gracefully', function () {
    $fixture = loadFixture('webhook/updated.json');
    $fixture['status'] = 'unknown_status';

    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_xxxxx' => Http::sequence()
            ->push(loadFixture('webhook/created.json'))
            ->push($fixture),
    ]);

    $this->artisan('payrex:webhook-update', ['id' => 'wh_xxxxx'])
        ->expectsQuestion('Webhook URL', 'https://my-ecommerce.com/webhook-updated')
        ->expectsQuestion('Which events should this webhook listen to?', ['payment_intent.succeeded', 'refund.created'])
        ->expectsQuestion('Description', 'Updated webhook endpoint')
        ->assertSuccessful();
});
