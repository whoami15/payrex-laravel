<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\PayrexClient;

it('registers the payrex client as a singleton', function () {
    $client = app(PayrexClient::class);

    expect($client)->toBeInstanceOf(PayrexClient::class);
    expect(app(PayrexClient::class))->toBe($client);
});

it('resolves the payrex alias', function () {
    expect(app('payrex'))->toBeInstanceOf(PayrexClient::class);
});
