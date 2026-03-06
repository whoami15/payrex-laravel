<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Concerns\HasList;
use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

/**
 * @method PayrexCollection<BillingStatement> list(array<string, mixed> $params = [])
 */
final class BillingStatementResource extends ApiResource
{
    use HasList;

    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/billing_statements';
    }

    /**
     * Get the DTO class used to hydrate each item in the list.
     */
    protected function listItemClass(): string
    {
        return BillingStatement::class;
    }

    /**
     * Create a new billing statement.
     *
     * @param  array{
     *     customer_id: string,
     *     currency?: string,
     *     description?: string,
     *     due_at?: int,
     *     billing_details_collection?: string,
     *     metadata?: array<string, string>,
     *     payment_settings?: array{
     *         payment_methods?: array<string>,
     *         payment_method_options?: array{
     *             card?: array{
     *                 allowed_bins?: array<string>,
     *                 allowed_funding?: array<string>,
     *             },
     *         },
     *     },
     *     line_items?: array<array{
     *         description: string,
     *         unit_price: int,
     *         quantity: int,
     *     }>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params): BillingStatement
    {
        return BillingStatement::from($this->client->post($this->resourceUri(), $this->withDefaultCurrency($params)));
    }

    /**
     * Retrieve a billing statement by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): BillingStatement
    {
        return BillingStatement::from($this->client->get("{$this->resourceUri()}/{$id}"));
    }

    /**
     * Update a billing statement.
     *
     * @param  array{
     *     customer_id?: string,
     *     description?: string,
     *     due_at?: int,
     *     billing_details_collection?: string,
     *     metadata?: array<string, string>,
     *     payment_settings?: array{
     *         payment_methods?: array<string>,
     *         payment_method_options?: array<string, mixed>,
     *     },
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): BillingStatement
    {
        return BillingStatement::from($this->client->put("{$this->resourceUri()}/{$id}", $params));
    }

    /**
     * Delete a billing statement.
     *
     * @throws PayrexApiException
     */
    public function delete(string $id): DeletedResource
    {
        return DeletedResource::from($this->client->delete("{$this->resourceUri()}/{$id}"));
    }

    /**
     * Finalize a billing statement.
     *
     * @throws PayrexApiException
     */
    public function finalize(string $id): BillingStatement
    {
        return BillingStatement::from($this->client->post("{$this->resourceUri()}/{$id}/finalize"));
    }

    /**
     * Void a billing statement.
     *
     * @throws PayrexApiException
     */
    public function void(string $id): BillingStatement
    {
        return BillingStatement::from($this->client->post("{$this->resourceUri()}/{$id}/void"));
    }

    /**
     * Mark a billing statement as uncollectible.
     *
     * @throws PayrexApiException
     */
    public function markUncollectible(string $id): BillingStatement
    {
        return BillingStatement::from($this->client->post("{$this->resourceUri()}/{$id}/mark_uncollectible"));
    }

    /**
     * Send a billing statement to the customer.
     *
     * @throws PayrexApiException
     */
    public function send(string $id): BillingStatement
    {
        return BillingStatement::from($this->client->post("{$this->resourceUri()}/{$id}/send"));
    }
}
