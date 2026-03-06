<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Data\BillingStatementLineItem;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

final class BillingStatementLineItemResource extends ApiResource
{
    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/billing_statement_line_items';
    }

    /**
     * Create a new billing statement line item.
     *
     * @param  array{
     *     billing_statement_id: string,
     *     description: string,
     *     unit_price: int,
     *     quantity: int,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params): BillingStatementLineItem
    {
        return BillingStatementLineItem::from($this->client->post($this->resourceUri(), $params));
    }

    /**
     * Update a billing statement line item.
     *
     * @param  array{
     *     description?: string,
     *     unit_price?: int,
     *     quantity?: int,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): BillingStatementLineItem
    {
        return BillingStatementLineItem::from($this->client->put("{$this->resourceUri()}/{$id}", $params));
    }

    /**
     * Delete a billing statement line item.
     *
     * @throws PayrexApiException
     */
    public function delete(string $id): DeletedResource
    {
        return DeletedResource::from($this->client->delete("{$this->resourceUri()}/{$id}"));
    }
}
