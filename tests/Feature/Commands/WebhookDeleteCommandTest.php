<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('deletes a webhook endpoint after confirmation', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::sequence()
            ->push(loadFixture('webhook/created.json'))
            ->push(loadFixture('webhook/deleted.json')),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'yes')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint deleted successfully.');

    Http::assertSent(fn ($request) => $request->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'
        && $request->method() === 'DELETE'
    );
});

it('cancels webhook deletion when not confirmed', function () {
    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/created.json')),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'no')
        ->assertSuccessful()
        ->expectsOutputToContain('Deletion cancelled.');

    Http::assertNotSent(fn ($request) => $request->method() === 'DELETE');
});

it('handles unknown webhook status gracefully', function () {
    $fixture = loadFixture('webhook/created.json');
    $fixture['status'] = 'unknown_status';

    Http::fake([
        'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::sequence()
            ->push($fixture)
            ->push(loadFixture('webhook/deleted.json')),
    ]);

    $this->artisan('payrex:webhook-delete', ['id' => 'wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'])
        ->expectsConfirmation('Are you sure you want to delete this webhook endpoint?', 'yes')
        ->assertSuccessful()
        ->expectsOutputToContain('Webhook endpoint deleted successfully.');
});
