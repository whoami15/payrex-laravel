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
     * @param  array{limit?: int, before?: string, after?: string}  $params
     * @return PayrexCollection<PayoutTransaction>
     *
     * @throws PayrexApiException
     */
    public function list(string $payoutId, array $params = []): PayrexCollection
    {
        return new PayrexCollection(
            $this->transport->request('GET', "{$this->resourceUri()}/{$payoutId}/transactions", $params),
            PayoutTransaction::class,
            fn (array $pagination) => $this->list($payoutId, array_merge($params, $pagination)), // @phpstan-ignore argument.type
        );
    }
}
