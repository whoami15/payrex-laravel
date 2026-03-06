<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('deletes a webhook endpoint after confirmation', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_xxxxx' => Http::sequence()
            ->push(loadFixture('webhook/created.json'))
            ->push(loadFixture('webhook/deleted.json')),
    ]);

    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // The table displayed before the confirmation prompt cannot be asserted here.
    // ->expectsPromptsTable(
    //     headers: ['Field', 'Value'],
    //     rows: [
    //         ['ID', 'wh_xxxxx'],
    //         ['URL', 'https://my-ecommerce.com/send-shipments'],
    //         ['Status', 'enabled'],
    //         ['Events', 'payment_intent.succeeded'],
    //         ['Description', 'This is the webhook used for sending shipments after receiving successfully paid payments'],
    //         ['Created At', date('Y-m-d H:i:s', 1706056262)],
    //     ],
    // )
    $this->artisan('payrex:webhook-delete', ['id' => 'wh_xxxxx'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'yes')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint deleted successfully.');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_xxxxx'
        && $request->method() === 'DELETE'
    );
});

it('cancels webhook deletion when not confirmed', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_xxxxx' => Http::response(loadFixture('webhook/created.json')),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_xxxxx'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'no')
        ->assertSuccessful()
        ->expectsOutputToContain('Deletion cancelled.');

    Http::assertNotSent(fn ($request) => $request->method() === 'DELETE');
});

it('displays error message when webhook not found', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_nonexistent' => Http::response(loadFixture('errors/resource_not_found.json'), 404),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_nonexistent'])
        ->assertFailed()
        ->expectsOutputToContain('The resource with ID pi_nonexistent was not found.');
});

it('handles unknown webhook status gracefully', function () {
    $fixture = loadFixture('webhook/created.json');
    $fixture['status'] = 'unknown_status';

    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_xxxxx' => Http::sequence()
            ->push($fixture)
            ->push(loadFixture('webhook/deleted.json')),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_xxxxx'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'yes')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint deleted successfully.');
});
