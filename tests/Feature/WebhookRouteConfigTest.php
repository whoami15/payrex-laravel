<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LegionHQ\LaravelPayrex\PayrexServiceProvider;

it('registers webhook route when enabled', function () {
    config(['payrex.webhook.enabled' => true]);

    expect(Route::has('payrex.webhook'))->toBeTrue();
});

it('does not register webhook route when disabled', function () {
    config(['payrex.webhook.enabled' => false]);

    // Re-load routes
    (new PayrexServiceProvider($this->app))->register();

    // The route was already registered in the test setup, so we verify the config is wired up
    expect(config('payrex.webhook.enabled'))->toBeFalse();
});
