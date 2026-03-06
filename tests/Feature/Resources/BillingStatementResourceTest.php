<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

it('creates a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements' => Http::response(loadFixture('billing_statement/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->create([
        'customer_id' => 'cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22',
        'currency' => 'PHP',
        'billing_details_collection' => 'always',
        'payment_settings' => [
            'payment_methods' => ['card', 'gcash'],
        ],
        'line_items' => [
            [
                'description' => 'Product X',
                'unit_price' => 10000,
                'quantity' => 5,
            ],
        ],
    ]);

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->resource)->toBe('billing_statement')
        ->and($result->amount)->toBe(100000)
        ->and($result->currency)->toBe('PHP')
        ->and($result->customerId)->toBe('cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22')
        ->and($result->status)->toBe(BillingStatementStatus::Open)
        ->and($result->description)->toBeNull()
        ->and($result->url)->toBe('https://bill.payrexhq.com/b/test_bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS_secret_SKhaVPaqozBV97oeU4h1kw7MbqZKFKSL4ZTqZaYNMAHaqsvtcB')
        ->and($result->billingDetailsCollection)->toBe('always')
        ->and($result->dueAt)->toBe(1721813375)
        ->and($result->paymentIntent)->not->toBeNull()
        ->and($result['payment_settings']['payment_methods'])->toBe(['card', 'gcash'])
        ->and($result['line_items'])->toHaveCount(2)
        ->and($result['customer']['id'])->toBe('cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22')
        ->and($result['customer']['name'])->toBe('Juan Dela Cruz')
        ->and($result->livemode)->toBeFalse();

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/billing_statements'
            && $r->method() === 'POST'
            && $r['customer_id'] === 'cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22'
            && $r['currency'] === 'PHP';
    });
});

it('lists billing statements', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements' => Http::response(loadFixture('billing_statement/list.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->list();

    expect($result)->toBeInstanceOf(PayrexCollection::class)
        ->and($result['resource'])->toBe('list')
        ->and($result['data'])->toHaveCount(2)
        ->and($result['has_more'])->toBeFalse()
        ->and($result->data[0])->toBeInstanceOf(BillingStatement::class)
        ->and($result->data[0]->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->data[0]->status)->toBe(BillingStatementStatus::Open)
        ->and($result->data[1]->id)->toBe('bstm_BcwoSnG0s688CCGGuOtEGwYwDft2Dff2')
        ->and($result->data[1]->status)->toBe(BillingStatementStatus::Draft);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements'
        && $r->method() === 'GET'
    );
});

it('retrieves a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS' => Http::response(loadFixture('billing_statement/created.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->retrieve('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->amount)->toBe(100000)
        ->and($result->customerId)->toBe('cus_ZBBnR6Fsr5zvX7HFuFsLFZcvAes9JC22')
        ->and($result['line_items'])->toHaveCount(2)
        ->and($result['customer']['name'])->toBe('Juan Dela Cruz');

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS'
        && $r->method() === 'GET'
    );
});

it('updates a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS' => Http::response(loadFixture('billing_statement/updated.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->update('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS', [
        'description' => 'Updated billing statement description',
    ]);

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->description)->toBe('Updated billing statement description');

    Http::assertSent(function ($r) {
        return $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS'
            && $r->method() === 'PUT'
            && $r['description'] === 'Updated billing statement description';
    });
});

it('deletes a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS' => Http::response(loadFixture('billing_statement/deleted.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->delete('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(DeletedResource::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->deleted)->toBeTrue();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS'
        && $r->method() === 'DELETE'
    );
});

it('finalizes a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/finalize' => Http::response(loadFixture('billing_statement/finalized.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->finalize('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->status)->toBe(BillingStatementStatus::Open)
        ->and($result->paymentIntent)->not->toBeNull();

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/finalize'
        && $r->method() === 'POST'
    );
});

it('voids a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/void' => Http::response(loadFixture('billing_statement/voided.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->void('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->status)->toBe(BillingStatementStatus::Void);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/void'
        && $r->method() === 'POST'
    );
});

it('marks a billing statement as uncollectible', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/mark_uncollectible' => Http::response(loadFixture('billing_statement/uncollectible.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->markUncollectible('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->status)->toBe(BillingStatementStatus::Uncollectible);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/mark_uncollectible'
        && $r->method() === 'POST'
    );
});

it('sends a billing statement', function () {
    Http::fake(['https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/send' => Http::response(loadFixture('billing_statement/finalized.json'))]);

    $client = new PayrexClient(secretKey: 'sk_test_123', baseUrl: 'https://api.payrexhq.com');
    $result = $client->billingStatements()->send('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS');

    expect($result)->toBeInstanceOf(BillingStatement::class)
        ->and($result->id)->toBe('bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS')
        ->and($result->status)->toBe(BillingStatementStatus::Open);

    Http::assertSent(fn ($r) => $r->url() === 'https://api.payrexhq.com/billing_statements/bstm_AbvnRnF9r577BBFFuNsLFvXvLes1CeeS/send'
        && $r->method() === 'POST'
    );
});
