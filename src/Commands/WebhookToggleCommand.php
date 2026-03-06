<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

final class WebhookToggleCommand extends Command
{
    protected $signature = 'payrex:webhook-toggle {id : The webhook endpoint ID}';

    protected $description = 'Toggle a PayRex webhook endpoint between enabled and disabled';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        /** @var string $id */
        $id = $this->argument('id');

        $webhook = $client->webhooks()->retrieve($id);

        $toggled = $webhook->status === WebhookEndpointStatus::Enabled
            ? $client->webhooks()->disable($id)
            : $client->webhooks()->enable($id);

        $status = $toggled->status instanceof WebhookEndpointStatus
            ? $toggled->status->value
            : 'updated';

        $this->components->info("Webhook endpoint {$status} successfully.");
        $this->table(['Field', 'Value'], $this->webhookEndpointRows($toggled));

        return self::SUCCESS;
    }

    /**
     * Build table rows for displaying a webhook endpoint's details.
     *
     * @return list<array{0: string, 1: string}>
     */
    private function webhookEndpointRows(WebhookEndpoint $webhook): array
    {
        $status = $webhook->status instanceof WebhookEndpointStatus
            ? $webhook->status->value
            : '-';

        return [
            ['ID', $webhook->id],
            ['URL', $webhook->url ?? '-'],
            ['Status', $status],
            ['Events', implode(', ', $webhook->events ?? [])],
            ['Description', $webhook->description ?? '-'],
            ['Created At', $webhook->createdAt ? date('Y-m-d H:i:s', $webhook->createdAt) : '-'],
        ];
    }
}
