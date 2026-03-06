<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('payrex.secret_key', 'sk_test_12345');
        $app['config']->set('payrex.public_key', 'pk_test_12345');
        $app['config']->set('payrex.webhook.enabled', true);
        $app['config']->set('payrex.webhook.secret', 'whsec_test_secret');
    }
}
