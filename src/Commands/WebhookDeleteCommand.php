<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\confirm;

final class WebhookDeleteCommand extends Command
{
    protected $signature = 'payrex:webhook-delete {id : The webhook endpoint ID}';

    protected $description = 'Delete a PayRex webhook endpoint';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        /** @var string $id */
        $id = $this->argument('id');

        $webhook = $client->webhooks()->retrieve($id);

        $this->table(['Field', 'Value'], $this->webhookEndpointRows($webhook));

        if (! confirm(label: 'Are you sure you want to delete this webhook endpoint?', default: false)) {
            $this->components->warn('Deletion cancelled.');

            return self::SUCCESS;
        }

        $client->webhooks()->delete($id);

        $this->components->info('Webhook endpoint deleted successfully.');

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
