<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use LegionHQ\LaravelPayrex\PayrexClient;

final class WebhookListCommand extends Command
{
    protected $signature = 'payrex:webhook-list';

    protected $description = 'List all PayRex webhook endpoints';

    /**
     * Execute the console command.
     */
    public function handle(PayrexClient $client): int
    {
        $collection = $client->webhooks()->list();

        if (count($collection) === 0) {
            $this->components->warn('No webhook endpoints found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($collection->autoPaginate() as $item) {
            $rows[] = [
                $item->id,
                $item->url,
                $item->status?->value,
                implode(', ', $item->events ?? []),
                $item->createdAt ? date('Y-m-d H:i:s', $item->createdAt) : '-',
            ];
        }

        $this->table(['ID', 'URL', 'Status', 'Events', 'Created At'], $rows);

        return self::SUCCESS;
    }
}
