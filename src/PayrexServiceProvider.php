<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use LegionHQ\LaravelPayrex\Commands\WebhookCreateCommand;
use LegionHQ\LaravelPayrex\Commands\WebhookDeleteCommand;
use LegionHQ\LaravelPayrex\Commands\WebhookListCommand;
use LegionHQ\LaravelPayrex\Commands\WebhookTestCommand;
use LegionHQ\LaravelPayrex\Commands\WebhookToggleCommand;
use LegionHQ\LaravelPayrex\Commands\WebhookUpdateCommand;
use LegionHQ\LaravelPayrex\Middleware\VerifyWebhookSignature;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class PayrexServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the PayRex package resources and routes.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('payrex')
            ->hasConfigFile()
            ->hasMigration('add_payrex_customer_id_column')
            ->hasCommands([
                WebhookCreateCommand::class,
                WebhookDeleteCommand::class,
                WebhookListCommand::class,
                WebhookTestCommand::class,
                WebhookToggleCommand::class,
                WebhookUpdateCommand::class,
            ]);
    }

    /**
     * Conditionally load the webhook route after the package boots.
     */
    public function packageBooted(): void
    {
        if (config('payrex.webhook.enabled', false)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
        }
    }

    /**
     * Register the PayRex client singleton and webhook middleware.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(PayrexClient::class, function () {
            return new PayrexClient(
                secretKey: (string) config('payrex.secret_key', ''),
                baseUrl: config('payrex.api_base_url', 'https://api.payrexhq.com'),
                timeout: (int) config('payrex.timeout', 30),
                connectTimeout: (int) config('payrex.connect_timeout', 30),
                retries: (int) config('payrex.retries', 0),
                retryDelay: (int) config('payrex.retry_delay', 100),
                currency: (string) config('payrex.currency', 'PHP'),
                webhookSecret: (string) config('payrex.webhook.secret', ''),
            );
        });

        $this->app->alias(PayrexClient::class, 'payrex');

        $this->app->singleton(VerifyWebhookSignature::class, function () {
            return new VerifyWebhookSignature(
                webhookSecret: (string) config('payrex.webhook.secret', ''),
                tolerance: (int) config('payrex.webhook.tolerance', 300),
            );
        });
    }
}
