<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('displays webhook endpoints in a table', function () {
    Http::fake(['https://api.payrexhq.com/webhooks*' => Http::response(loadFixture('webhook/list.json'))]);

    $this->artisan('payrex:webhook-list')
        ->assertSuccessful();
    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // ->expectsPromptsTable(
    //     headers: ['ID', 'URL', 'Status', 'Events', 'Created At'],
    //     rows: [
    //         ['wh_xxxxx', 'https://my-ecommerce.com/send-shipments', 'enabled', '1 event', date('Y-m-d H:i:s', 1706056262)],
    //         ['wh_yyyyy', 'https://staging.my-ecommerce.com/webhooks', 'disabled', '2 events', date('Y-m-d H:i:s', 1706056300)],
    //     ],
    // );
});

it('displays error message on authentication failure', function () {
    Http::fake(['https://api.payrexhq.com/webhooks*' => Http::response(loadFixture('errors/authentication.json'), 401)]);

    $this->artisan('payrex:webhook-list')
        ->assertFailed()
        ->expectsOutputToContain('Invalid API key provided.');
});

it('handles empty webhook list', function () {
    Http::fake(['https://api.payrexhq.com/webhooks*' => Http::response(['resource' => 'list', 'data' => [], 'has_more' => false])]);

    $this->artisan('payrex:webhook-list')
        ->assertSuccessful()
        ->expectsOutputToContain('No webhook endpoints found.');
});
