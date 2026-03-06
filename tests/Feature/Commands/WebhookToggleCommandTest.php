<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('disables an enabled webhook endpoint', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/created.json')),
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/disable' => Http::response(loadFixture('webhook/disabled.json')),
    ]);

    $this->artisan('payrex:webhook-toggle', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint disabled successfully.');
    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // ->expectsPromptsTable(
    //     headers: ['Field', 'Value'],
    //     rows: [
    //         ['ID', 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'],
    //         ['URL', 'https://my-ecommerce.com/send-shipments'],
    //         ['Status', 'disabled'],
    //         ['Events', 'payment_intent.succeeded'],
    //         ['Description', 'This is the webhook used for sending shipments after receiving successfully paid payments'],
    //         ['Created At', date('Y-m-d H:i:s', 1706056262)],
    //     ],
    // );

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/disable'
        && $request->method() === 'POST'
    );
});

it('enables a disabled webhook endpoint', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/disabled.json')),
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable' => Http::response(loadFixture('webhook/enabled.json')),
    ]);

    $this->artisan('payrex:webhook-toggle', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint enabled successfully.');
    // TODO: expectsPromptsTable() was introduced in Laravel 12 and is not available in Laravel 11.
    // ->expectsPromptsTable(
    //     headers: ['Field', 'Value'],
    //     rows: [
    //         ['ID', 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'],
    //         ['URL', 'https://my-ecommerce.com/send-shipments'],
    //         ['Status', 'enabled'],
    //         ['Events', 'payment_intent.succeeded'],
    //         ['Description', 'This is the webhook used for sending shipments after receiving successfully paid payments'],
    //         ['Created At', date('Y-m-d H:i:s', 1706056262)],
    //     ],
    // );

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable'
        && $request->method() === 'POST'
    );
});

it('handles unknown webhook status gracefully', function () {
    $retrieveFixture = loadFixture('webhook/disabled.json');
    $retrieveFixture['status'] = 'unknown_status';

    $enableFixture = loadFixture('webhook/enabled.json');
    $enableFixture['status'] = 'unknown_status';

    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response($retrieveFixture),
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable' => Http::response($enableFixture),
    ]);

    $this->artisan('payrex:webhook-toggle', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint updated successfully.');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable'
        && $request->method() === 'POST'
    );
});
