<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\PayrexClient;

it('returns a PayrexCollection from the list trait', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(Customer::class);
});

it('passes params through to the API request', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->customers()->list(['limit' => 5, 'after' => 'cus_test_100']);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), '/customers')
            && str_contains($r->url(), 'limit=5')
            && str_contains($r->url(), 'after=cus_test_100')
            && $r->method() === 'GET';
    });
});

it('supports auto-pagination across multiple pages', function () {
    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, 'after=cus_2')) {
            return Http::response([
                'resource' => 'list',
                'has_more' => false,
                'data' => [
                    ['id' => 'cus_3', 'resource' => 'customer', 'name' => 'Customer 3', 'email' => 'c3@example.com', 'currency' => 'PHP', 'livemode' => false, 'created_at' => 1700000000, 'updated_at' => 1700000000],
                ],
            ]);
        }

        return Http::response([
            'resource' => 'list',
            'has_more' => true,
            'data' => [
                ['id' => 'cus_1', 'resource' => 'customer', 'name' => 'Customer 1', 'email' => 'c1@example.com', 'currency' => 'PHP', 'livemode' => false, 'created_at' => 1700000000, 'updated_at' => 1700000000],
                ['id' => 'cus_2', 'resource' => 'customer', 'name' => 'Customer 2', 'email' => 'c2@example.com', 'currency' => 'PHP', 'livemode' => false, 'created_at' => 1700000000, 'updated_at' => 1700000000],
            ],
        ]);
    });

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $allIds = $client->customers()->list()->autoPaginate()->map(fn ($item) => $item->id)->all();

    expect($allIds)->toBe(['cus_1', 'cus_2', 'cus_3']);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'after=cus_2');
    });
});
