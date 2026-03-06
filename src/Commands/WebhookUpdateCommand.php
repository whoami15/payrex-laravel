<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\form;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\table;
use function Laravel\Prompts\task;

final class WebhookUpdateCommand extends Command
{
    protected $signature = 'payrex:webhook-update {id : The webhook endpoint ID}';

    protected $description = 'Update an existing PayRex webhook endpoint';

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

            $this->components->info("Updating webhook endpoint: {$webhook->id}");

            $responses = form()
                ->text(
                    label: 'Webhook URL',
                    default: $webhook->url ?? '',
                    required: 'The webhook URL is required.',
                    validate: ['url'],
                    name: 'url',
                )
                ->add(function () use ($webhook) {
                    return multiselect(
                        label: 'Which events should this webhook listen to?',
                        options: $this->webhookEventOptions(),
                        default: $webhook->events ?? [],
                        scroll: 12,
                        required: 'You must select at least one event.',

                    );
                }, name: 'events')
                ->text(
                    label: 'Description',
                    default: $webhook->description ?? '',
                    name: 'description',
                )
                ->submit();

            /** @var WebhookEndpoint $updated */
            $updated = task(
                label: 'Saving changes...',
                callback: fn () => $client->webhooks()->update($id, [
                    'url' => $responses['url'],
                    'events' => $responses['events'],
                    'description' => $responses['description'],
                ]),
            );

            $this->components->info('Webhook endpoint updated successfully.');

            table(
                headers: ['Field', 'Value'],
                rows: $this->webhookEndpointRows($updated),
            );

            return self::SUCCESS;
        } catch (PayrexApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
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
            ['Events', implode(PHP_EOL, $webhook->events ?? [])],
            ['Description', $webhook->description ?? '-'],
            ['Created At', $webhook->createdAt ? date('Y-m-d H:i:s', $webhook->createdAt) : '-'],
        ];
    }
}
