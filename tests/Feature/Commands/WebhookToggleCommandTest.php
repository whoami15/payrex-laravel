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
