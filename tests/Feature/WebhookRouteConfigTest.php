<?php

declare(strict_types=1);

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use LegionHQ\LaravelPayrex\PayrexServiceProvider;

it('registers webhook route when enabled', function () {
    expect(Route::has('payrex.webhook'))->toBeTrue();
});

it('uses the default webhook path', function () {
    $route = Route::getRoutes()->getByName('payrex.webhook');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('payrex/webhook');
});

it('does not register webhook route when disabled', function () {
    config(['payrex.webhook.enabled' => false]);

    // Clear all routes and re-boot only the package's route registration
    $this->app['router']->setRoutes(new RouteCollection);
    (new PayrexServiceProvider($this->app))->packageBooted();

    expect(Route::has('payrex.webhook'))->toBeFalse();
});
