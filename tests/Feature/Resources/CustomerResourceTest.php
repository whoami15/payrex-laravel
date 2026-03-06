<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers' => Http::response(loadFixture('customer/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->create([
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@gmail.com',
        'currency' => 'PHP',
    ]);

    expect($result)->toBeInstanceOf(Customer::class)
        ->and($result->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->resource)->toBe('customer')
        ->and($result->name)->toBe('Juan Dela Cruz')
        ->and($result->email)->toBe('juan@gmail.com')
        ->and($result->currency)->toBe('PHP')
        ->and($result->billingStatementPrefix)->toBe('PKYG9MA2')
        ->and($result->nextBillingStatementSequenceNumber)->toBe('1')
        ->and($result->billing)->toBeNull()
        ->and($result->metadata)->toBeNull()
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/customers'
            && $r->method() === 'POST'
            && $r['name'] === 'Juan Dela Cruz'
            && $r['email'] === 'juan@gmail.com'
            && $r['currency'] === 'PHP';
    });
});

it('lists customers', function () {
    Http::fake(['https://api.payrexhq.com/customers*' => Http::response(loadFixture('customer/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->list([
        'limit' => 10,
    ]);

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result['resource'])->toBe('list')
        ->and($result['data'])->toHaveCount(2)
        ->and($result['has_more'])->toBeFalse()
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(Customer::class)
        ->and($result->data[0]->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->data[0]->name)->toBe('Juan Dela Cruz')
        ->and($result->data[1]->id)->toBe('cus_CcYoSnG0s688CCGGuOtEGwYwCft2Bff2')
        ->and($result->data[1]->name)->toBe('Maria Santos');

    Http::assertSent(fn ($r) => str_contains($r->url(), '/customers')
        && $r->method() === 'GET'
    );
});

it('retrieves a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1' => Http::response(loadFixture('customer/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->retrieve('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1');

    expect($result)->toBeInstanceOf(Customer::class)
        ->and($result->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->billingStatementPrefix)->toBe('PKYG9MA2')
        ->and($result->billing)->toBeNull();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1'
        && $r->method() === 'GET'
    );
});

it('updates a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1' => Http::response(loadFixture('customer/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->update('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1', [
        'name' => 'Juan Dela Cruz Jr.',
        'email' => 'juan.jr@gmail.com',
        'billing_details' => [
            'phone' => '+639181234567',
            'address' => [
                'line1' => '456 Updated St',
                'city' => 'Quezon City',
                'state' => 'NCR',
                'postal_code' => '1100',
                'country' => 'PH',
            ],
        ],
    ]);

    expect($result)->toBeInstanceOf(Customer::class)
        ->and($result->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->name)->toBe('Juan Dela Cruz Jr.')
        ->and($result->email)->toBe('juan.jr@gmail.com')
        ->and($result->billing)->not->toBeNull()
        ->and($result['billing']['phone'])->toBe('+639181234567')
        ->and($result['billing']['address']['city'])->toBe('Quezon City');

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1'
            && $r->method() === 'PUT'
            && $r['name'] === 'Juan Dela Cruz Jr.'
            && $r['email'] === 'juan.jr@gmail.com';
    });
});

it('deletes a customer', function () {
    Http::fake(['https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1' => Http::response(loadFixture('customer/deleted.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->customers()->delete('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1');

    expect($result)->toBeInstanceOf(DeletedResource::class)
        ->and($result->id)->toBe('cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1')
        ->and($result->deleted)->toBeTrue();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/customers/cus_BbXnRnF9r577BBFFuNsDFvXvBes1Aee1'
        && $r->method() === 'DELETE'
    );
});
