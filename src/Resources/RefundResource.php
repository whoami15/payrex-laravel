<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Data\Refund;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

final class RefundResource extends ApiResource
{
    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/refunds';
    }

    /**
     * Create a new refund.
     *
     * @param  array{
     *     payment_id: string,
     *     amount: int,
     *     currency?: string,
     *     reason: string,
     *     description?: string,
     *     remarks?: string,
     *     metadata?: array<string, string>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params, ?string $idempotencyKey = null): Refund
    {
        return Refund::from($this->client->post($this->resourceUri(), $this->withDefaultCurrency($params), $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Update a refund.
     *
     * @param  array{
     *     metadata?: array<string, string>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): Refund
    {
        return Refund::from($this->client->put("{$this->resourceUri()}/{$id}", $params));
    }
}
