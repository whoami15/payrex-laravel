<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;

final class WebhookTestCommand extends Command
{
    protected $signature = 'payrex:webhook-test {type : The event type to simulate (e.g. payment_intent.succeeded)}';

    protected $description = 'Dispatch a synthetic webhook event locally for testing listeners';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = (string) $this->argument('type');

        $valid = array_column(WebhookEventType::cases(), 'value');

        if (! in_array($type, $valid, true)) {
            $this->error("Invalid event type: {$type}");
            $this->line('Valid types: '.implode(', ', $valid));

            return self::FAILURE;
        }

        $resourceType = Str::before($type, '.');

        $payload = [
            'id' => 'evt_test_'.Str::random(8),
            'resource' => 'event',
            'type' => $type,
            'livemode' => false,
            'pending_webhooks' => 0,
            'data' => [
                'resource' => [
                    'id' => 'res_test_'.Str::random(8),
                    'resource' => $resourceType,
                    'amount' => 10000,
                    'currency' => 'PHP',
                    'status' => Str::after($type, '.'),
                    'livemode' => false,
                    'metadata' => null,
                    'created_at' => time(),
                    'updated_at' => time(),
                ],
            ],
            'created_at' => time(),
            'updated_at' => time(),
        ];

        PayrexEvent::dispatchWebhook($payload);

        $this->info("Dispatched {$type} event successfully.");

        return self::SUCCESS;
    }
}
