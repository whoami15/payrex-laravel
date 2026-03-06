<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\PayrexClient;

use function Laravel\Prompts\table;
use function Laravel\Prompts\task;

final class WebhookListCommand extends Command
{
    protected $signature = 'payrex:webhook-list';

    protected $description = 'List all PayRex webhook endpoints';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        try {
            /** @var list<array<int, string>> $rows */
            $rows = task(
                label: 'Fetching webhook endpoints...',
                callback: function () use ($client): array {
                    $rows = [];

                    foreach ($client->webhooks()->list()->autoPaginate() as $item) {
                        $rows[] = [
                            $item->id,
                            $item->url ?? '-',
                            $item->status instanceof WebhookEndpointStatus ? $item->status->value : '-',
                            ($count = count($item->events ?? [])).' '.($count === 1 ? 'event' : 'events'),
                            $item->createdAt ? date('Y-m-d H:i:s', $item->createdAt) : '-',
                        ];
                    }

                    return $rows;
                },
            );

            if ($rows === []) {
                $this->components->warn('No webhook endpoints found.');

                return self::SUCCESS;
            }

            table(
                headers: ['ID', 'URL', 'Status', 'Events', 'Created At'],
                rows: $rows,
            );

            return self::SUCCESS;
        } catch (PayrexApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
