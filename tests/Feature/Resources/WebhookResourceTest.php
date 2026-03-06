<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->create([
        'url' => 'https://my-ecommerce.com/send-shipments',
        'events' => ['payment_intent.succeeded'],
        'description' => 'This is the webhook used for sending shipments after receiving successfully paid payments',
    ]);

    expect($result)->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->resource)->toBe('webhook')
        ->and($result->secretKey)->toBe('whsk_cU8kMThbLEkF3yvz1ygCrPrBdAWguuCU')
        ->and($result->url)->toBe('https://my-ecommerce.com/send-shipments')
        ->and($result->events)->toBe(['payment_intent.succeeded'])
        ->and($result->description)->toBe('This is the webhook used for sending shipments after receiving successfully paid payments')
        ->and($result->status)->toBe(WebhookEndpointStatus::Enabled)
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/webhooks'
            && $r->method() === 'POST'
            && $r['url'] === 'https://my-ecommerce.com/send-shipments';
    });
});

it('lists webhook endpoints', function () {
    Http::fake(['https://api.payrexhq.com/webhooks' => Http::response(loadFixture('webhook/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result['resource'])->toBe('list')
        ->and($result['data'])->toHaveCount(2)
        ->and($result['has_more'])->toBeFalse()
        ->and($result->data[0])->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->data[0]->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->data[0]->url)->toBe('https://my-ecommerce.com/send-shipments')
        ->and($result->data[0]->status)->toBe(WebhookEndpointStatus::Enabled)
        ->and($result->data[1]->id)->toBe('wh_336uNdsVNNejxw3Zb8IUYBFjgBy9oop3')
        ->and($result->data[1]->status)->toBe(WebhookEndpointStatus::Disabled);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/webhooks'
        && $r->method() === 'GET'
    );
});

it('retrieves a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->retrieve('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2');

    expect($result)->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->secretKey)->toBe('whsk_cU8kMThbLEkF3yvz1ygCrPrBdAWguuCU')
        ->and($result->url)->toBe('https://my-ecommerce.com/send-shipments')
        ->and($result->events)->toBe(['payment_intent.succeeded']);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'
        && $r->method() === 'GET'
    );
});

it('updates a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->update('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2', [
        'url' => 'https://my-ecommerce.com/webhook-updated',
        'events' => ['payment_intent.succeeded', 'refund.created'],
        'description' => 'Updated webhook endpoint',
    ]);

    expect($result)->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->url)->toBe('https://my-ecommerce.com/webhook-updated')
        ->and($result->description)->toBe('Updated webhook endpoint')
        ->and($result->events)->toBe(['payment_intent.succeeded', 'refund.created']);

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'
            && $r->method() === 'PUT'
            && $r['url'] === 'https://my-ecommerce.com/webhook-updated';
    });
});

it('deletes a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2' => Http::response(loadFixture('webhook/deleted.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->delete('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2');

    expect($result)->toBeInstanceOf(DeletedResource::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->deleted)->toBeTrue();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2'
        && $r->method() === 'DELETE'
    );
});

it('enables a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable' => Http::response(loadFixture('webhook/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->enable('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2');

    expect($result)->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->status)->toBe(WebhookEndpointStatus::Enabled);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/enable'
        && $r->method() === 'POST'
    );
});

it('disables a webhook endpoint', function () {
    Http::fake(['https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/disable' => Http::response(loadFixture('webhook/disabled.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->webhooks()->disable('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2');

    expect($result)->toBeInstanceOf(WebhookEndpoint::class)
        ->and($result->id)->toBe('wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2')
        ->and($result->status)->toBe(WebhookEndpointStatus::Disabled);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/webhooks/wh_225tMcrUMMdiwv2Ya7HTXAEifAx8nno2/disable'
        && $r->method() === 'POST'
    );
});
