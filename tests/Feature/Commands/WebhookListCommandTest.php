<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('displays webhook endpoints in a table', function () {
    Http::fake(['https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/list.json'))]);

    $this->artisan('payrex:webhook-list')
        ->assertSuccessful()
        ->expectsTable(
            ['ID', 'URL', 'Status', 'Events', 'Created At'],
            [
                ['wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2', 'https://my-ecommerce.com/send-shipments', 'enabled', 'payment_intent.succeeded', date('Y-m-d H:i:s', 1706056262)],
                ['wh_336uNdsVNNejxw3Zb8IUYBFjgBy9oop3', 'https://staging.my-ecommerce.com/webhooks', 'disabled', 'billing_statement.created, billing_statement.paid', date('Y-m-d H:i:s', 1706056300)],
            ],
        );
});

it('handles empty webhook list', function () {
    Http::fake(['https://api.payrexhq.com/webhooks' => Http::response(['resource' => 'list', 'data' => [], 'has_more' => false])]);

    $this->artisan('payrex:webhook-list')
        ->assertSuccessful()
        ->expectsOutputToContain('No webhook endpoints found.');
});
