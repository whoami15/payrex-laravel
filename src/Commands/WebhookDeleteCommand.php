<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\table;
use function Laravel\Prompts\task;

final class WebhookDeleteCommand extends Command
{
    protected $signature = 'payrex:webhook-delete {id : The webhook endpoint ID}';

    protected $description = 'Delete a PayRex webhook endpoint';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        try {
            /** @var string $id */
            $id = $this->argument('id');

            /** @var WebhookEndpoint $webhook */
            $webhook = task(
                label: 'Fetching webhook endpoint...',
                callback: fn () => $client->webhooks()->retrieve($id),
            );

            table(
                headers: ['Field', 'Value'],
                rows: $this->webhookEndpointRows($webhook),
            );

            if (! confirm(label: 'Are you sure you want to delete this webhook endpoint?', default: false)) {
                $this->components->warn('Deletion cancelled.');

                return self::SUCCESS;
            }

            task(
                label: 'Deleting webhook endpoint...',
                callback: fn () => $client->webhooks()->delete($id),
            );

            $this->components->info('Webhook endpoint deleted successfully.');

            return self::SUCCESS;
        } catch (PayrexApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
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
            ['ID', $webhook->id ?? ''],
            ['URL', $webhook->url ?? '-'],
            ['Status', $status],
            ['Events', implode(PHP_EOL, $webhook->events ?? [])],
            ['Description', $webhook->description ?? '-'],
            ['Created At', $webhook->createdAt ? date('Y-m-d H:i:s', $webhook->createdAt) : '-'],
        ];
    }
}
