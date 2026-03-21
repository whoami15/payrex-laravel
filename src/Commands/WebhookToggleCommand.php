<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\table;
use function Laravel\Prompts\task;

final class WebhookToggleCommand extends Command
{
    protected $signature = 'payrex:webhook-toggle {id : The webhook endpoint ID}';

    protected $description = 'Toggle a PayRex webhook endpoint between enabled and disabled';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        try {
            /** @var string $id */
            $id = $this->argument('id');

            /** @var WebhookEndpoint $toggled */
            $toggled = task(
                label: 'Toggling webhook endpoint...',
                callback: function () use ($client, $id) {
                    $webhook = $client->webhooks()->retrieve($id);

                    return $webhook->status === WebhookEndpointStatus::Enabled
                        ? $client->webhooks()->disable($id)
                        : $client->webhooks()->enable($id);
                },
            );

            $status = $toggled->status instanceof WebhookEndpointStatus
                ? $toggled->status->value
                : 'updated';

            $this->components->info("Webhook endpoint {$status} successfully.");

            table(
                headers: ['Field', 'Value'],
                rows: $this->webhookEndpointRows($toggled),
            );

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
