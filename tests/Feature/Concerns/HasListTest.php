<?php

declare(strict_types=1);

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Data\PayrexCursorPaginator;
use LegionHQ\LaravelPayrex\PayrexClient;

afterEach(function () {
    CursorPaginator::currentCursorResolver(fn () => null);
});

it('returns a PayrexCollection from the list trait', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(Customer::class);

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/customers'
            && $r->method() === 'GET';
    });
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

it('returns a PayrexCursorPaginator from paginate()', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $paginator = $client->customers()->paginate(perPage: 10);

    expect($paginator)->toBeInstanceOf(PayrexCursorPaginator::class)
        ->and($paginator->items())->toHaveCount(2)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->items()[0])->toBeInstanceOf(Customer::class);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), '/customers')
            && str_contains($r->url(), 'limit=10')
            && $r->method() === 'GET';
    });
});

it('passes filter params through paginate()', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->customers()->paginate(perPage: 5, params: ['name' => 'Juan']);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'limit=5')
            && str_contains($r->url(), 'name=Juan')
            && $r->method() === 'GET';
    });
});

it('paginate sets limit param from perPage', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->customers()->paginate(perPage: 25);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'limit=25');
    });
});

it('paginate forwards hasMore from the API response', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response([
        'resource' => 'list',
        'has_more' => true,
        'data' => [
            ['id' => 'cus_1', 'resource' => 'customer', 'name' => 'Customer 1', 'email' => 'c1@example.com', 'currency' => 'PHP', 'livemode' => false, 'created_at' => 1700000000, 'updated_at' => 1700000000],
        ],
    ])]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $paginator = $client->customers()->paginate(perPage: 1);

    expect($paginator->hasMorePages())->toBeTrue()
        ->and($paginator->nextCursor())->not->toBeNull()
        ->and($paginator->nextCursor()->parameter('id'))->toBe('cus_1');

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'limit=1')
            && $r->method() === 'GET';
    });
});

it('paginate returns no next cursor when API has no more pages', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $paginator = $client->customers()->paginate(perPage: 10);

    expect($paginator->hasMorePages())->toBeFalse()
        ->and($paginator->nextCursor())->toBeNull();

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'limit=10')
            && $r->method() === 'GET';
    });
});

it('paginate sends after param when cursor points to next items', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    CursorPaginator::currentCursorResolver(fn () => new Cursor(['id' => 'cus_abc'], pointsToNextItems: true));

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->customers()->paginate(perPage: 5);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'after=cus_abc')
            && str_contains($r->url(), 'limit=5')
            && $r->method() === 'GET';
    });
});

it('paginate sends before param when cursor points to previous items', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    CursorPaginator::currentCursorResolver(fn () => new Cursor(['id' => 'cus_xyz'], pointsToNextItems: false));

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $client->customers()->paginate(perPage: 5);

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'before=cus_xyz')
            && str_contains($r->url(), 'limit=5')
            && $r->method() === 'GET';
    });
});

it('paginate resolves path from the current request', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $paginator = $client->customers()->paginate(perPage: 10);

    expect($paginator->path())->toContain('http://');

    Http::assertSent(function ($r) {
        return str_contains($r->url(), '/customers')
            && $r->method() === 'GET';
    });
});
