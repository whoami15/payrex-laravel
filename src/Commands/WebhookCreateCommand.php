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

final class WebhookCreateCommand extends Command
{
    protected $signature = 'payrex:webhook-create';

    protected $description = 'Create a new PayRex webhook endpoint';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        try {
            $responses = form()
                ->text(
                    label: 'Webhook URL',
                    placeholder: 'https://example.com/webhooks/payrex',
                    required: 'The webhook URL is required.',
                    validate: ['url'],
                    name: 'url',
                )
                ->add(function () {
                    return multiselect(
                        label: 'Which events should this webhook listen to?',
                        options: $this->webhookEventOptions(),
                        scroll: 12,
                        required: 'You must select at least one event.',

                    );
                }, name: 'events')
                ->text(
                    label: 'Description',
                    placeholder: 'Optional description for this webhook endpoint',
                    name: 'description',
                )
                ->submit();

            /** @var WebhookEndpoint $webhook */
            $webhook = task(
                label: 'Creating webhook endpoint...',
                callback: function () use ($client, $responses) {
                    $params = [
                        'url' => $responses['url'],
                        'events' => $responses['events'],
                    ];

                    if ($responses['description'] !== '') {
                        $params['description'] = $responses['description'];
                    }

                    return $client->webhooks()->create($params);
                },
            );

            $this->components->info('Webhook endpoint created successfully.');

            table(
                headers: ['Field', 'Value'],
                rows: $this->webhookEndpointRows($webhook, showSecretKey: true),
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
    private function webhookEndpointRows(WebhookEndpoint $webhook, bool $showSecretKey = false): array
    {
        $status = $webhook->status instanceof WebhookEndpointStatus
            ? $webhook->status->value
            : '-';

        $rows = [
            ['ID', $webhook->id ?? ''],
            ['URL', $webhook->url ?? '-'],
            ['Status', $status],
            ['Events', implode(PHP_EOL, $webhook->events ?? [])],
            ['Description', $webhook->description ?? '-'],
        ];

        if ($showSecretKey && $webhook->secretKey !== null) {
            $rows[] = ['Secret Key', $webhook->secretKey];
        }

        $rows[] = ['Created At', $webhook->createdAt ? date('Y-m-d H:i:s', $webhook->createdAt) : '-'];

        return $rows;
    }
}
