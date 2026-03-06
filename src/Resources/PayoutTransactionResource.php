<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Data\PayoutTransaction;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

final class PayoutTransactionResource extends ApiResource
{
    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/payouts';
    }

    /**
     * List transactions for a payout.
     *
     * @param  array<string, mixed>  $params
     * @return PayrexCollection<PayoutTransaction>
     *
     * @throws PayrexApiException
     */
    public function list(string $payoutId, array $params = []): PayrexCollection
    {
        return new PayrexCollection(
            $this->client->get("{$this->resourceUri()}/{$payoutId}/transactions", $params),
            PayoutTransaction::class,
            /** @param array<string, string> $pagination */
            fn (array $pagination) => $this->list($payoutId, array_merge($params, $pagination)),
        );
    }
}
