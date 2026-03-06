<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;

final class WebhookUpdateCommand extends Command
{
    protected $signature = 'payrex:webhook-update {id : The webhook endpoint ID}';

    protected $description = 'Update an existing PayRex webhook endpoint';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        /** @var string $id */
        $id = $this->argument('id');

        $webhook = $client->webhooks()->retrieve($id);

        $this->components->info("Updating webhook endpoint: {$webhook->id}");

        $url = text(
            label: 'Webhook URL',
            default: $webhook->url ?? '',
            required: 'The webhook URL is required.',
            validate: ['url'],
        );

        /** @var list<string> $events */
        $events = multiselect(
            label: 'Which events should this webhook listen to?',
            options: $this->webhookEventOptions(),
            default: $webhook->events ?? [],
            scroll: 12,
            required: 'You must select at least one event.',
        );

        $description = text(
            label: 'Description',
            default: $webhook->description ?? '',
        );

        $updated = $client->webhooks()->update($id, [
            'url' => $url,
            'events' => $events,
            'description' => $description,
        ]);

        $this->components->info('Webhook endpoint updated successfully.');
        $this->table(['Field', 'Value'], $this->webhookEndpointRows($updated));

        return self::SUCCESS;
    }

    /**
     * Get the available webhook event types as multiselect options.
     *
     * @return array<string, string>
     */
    private function webhookEventOptions(): array
    {
        $options = [];

        foreach (WebhookEventType::cases() as $case) {
            $group = Str::headline(Str::before($case->value, '.'));
            $options[$case->value] = $group.' — '.Str::after($case->value, '.');
        }

        return $options;
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
