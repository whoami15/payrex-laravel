<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Concerns\HasList;
use LegionHQ\LaravelPayrex\Data\BillingStatement;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

/**
 * @method PayrexCollection<BillingStatement> list(array{limit?: int, before?: string, after?: string} $params = [])
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
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params, ?string $idempotencyKey = null): BillingStatement
    {
        return BillingStatement::from($this->transport->request('POST', $this->resourceUri(), $this->withDefaultCurrency($params), $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Retrieve a billing statement by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): BillingStatement
    {
        return BillingStatement::from($this->transport->request('GET', "{$this->resourceUri()}/{$id}"));
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
     *     payment_settings: array{
     *         payment_methods?: array<string>,
     *         payment_method_options?: array{
     *             card?: array{
     *                 allowed_bins?: array<string>,
     *                 allowed_funding?: array<string>,
     *             },
     *         },
     *     },
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): BillingStatement
    {
        return BillingStatement::from($this->transport->request('PUT', "{$this->resourceUri()}/{$id}", $params));
    }

    /**
     * Delete a billing statement.
     *
     * @throws PayrexApiException
     */
    public function delete(string $id): DeletedResource
    {
        return DeletedResource::from($this->transport->request('DELETE', "{$this->resourceUri()}/{$id}"));
    }

    /**
     * Finalize a billing statement.
     *
     * @throws PayrexApiException
     */
    public function finalize(string $id, ?string $idempotencyKey = null): BillingStatement
    {
        return BillingStatement::from($this->transport->request('POST', "{$this->resourceUri()}/{$id}/finalize", [], $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Void a billing statement.
     *
     * @throws PayrexApiException
     */
    public function void(string $id, ?string $idempotencyKey = null): BillingStatement
    {
        return BillingStatement::from($this->transport->request('POST', "{$this->resourceUri()}/{$id}/void", [], $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Mark a billing statement as uncollectible.
     *
     * @throws PayrexApiException
     */
    public function markUncollectible(string $id, ?string $idempotencyKey = null): BillingStatement
    {
        return BillingStatement::from($this->transport->request('POST', "{$this->resourceUri()}/{$id}/mark_uncollectible", [], $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Send a billing statement to the customer via email.
     *
     * The PayRex API documents this endpoint as returning a billing
     * statement resource, but currently returns an empty response.
     * When the response is empty, we fall back to a retrieve call
     * so consumers always get the expected BillingStatement back.
     *
     * @throws PayrexApiException
     */
    public function send(string $id, ?string $idempotencyKey = null): BillingStatement
    {
        // return BillingStatement::from($this->transport->request('POST',"{$this->resourceUri()}/{$id}/send", [], $this->idempotencyHeader($idempotencyKey)));

        $response = $this->transport->request('POST', "{$this->resourceUri()}/{$id}/send", [], $this->idempotencyHeader($idempotencyKey));

        if ($response === []) {
            return $this->retrieve($id);
        }

        return BillingStatement::from($response);
    }
}
