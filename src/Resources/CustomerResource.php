<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Concerns\HasList;
use LegionHQ\LaravelPayrex\Data\Customer;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

/**
 * @method PayrexCollection<Customer> list(array{limit?: int, before?: string, after?: string, email?: string, name?: string, metadata?: array<string, string>} $params = [])
 */
final class CustomerResource extends ApiResource
{
    use HasList;

    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/customers';
    }

    /**
     * Get the DTO class used to hydrate each item in the list.
     */
    protected function listItemClass(): string
    {
        return Customer::class;
    }

    /**
     * Create a new customer.
     *
     * @param  array{
     *     name: string,
     *     email: string,
     *     currency?: string,
     *     billing_details?: array{
     *         phone?: string,
     *         address?: array{
     *             line1?: string,
     *             line2?: string,
     *             city?: string,
     *             state?: string,
     *             postal_code?: string,
     *             country?: string,
     *         },
     *     },
     *     billing_statement_prefix?: string,
     *     next_billing_statement_sequence_number?: int,
     *     metadata?: array<string, string>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params, ?string $idempotencyKey = null): Customer
    {
        return Customer::from($this->transport->request('POST', $this->resourceUri(), $this->withDefaultCurrency($params), $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Retrieve a customer by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): Customer
    {
        return Customer::from($this->transport->request('GET', "{$this->resourceUri()}/{$id}"));
    }

    /**
     * Update a customer.
     *
     * @param  array{
     *     name?: string,
     *     email?: string,
     *     currency?: string,
     *     billing_details?: array{
     *         phone?: string,
     *         address?: array{
     *             line1?: string,
     *             line2?: string,
     *             city?: string,
     *             state?: string,
     *             postal_code?: string,
     *             country?: string,
     *         },
     *     },
     *     billing_statement_prefix?: string,
     *     next_billing_statement_sequence_number?: int,
     *     metadata?: array<string, string>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): Customer
    {
        return Customer::from($this->transport->request('PUT', "{$this->resourceUri()}/{$id}", $params));
    }

    /**
     * Delete a customer.
     *
     * @throws PayrexApiException
     */
    public function delete(string $id): DeletedResource
    {
        return DeletedResource::from($this->transport->request('DELETE', "{$this->resourceUri()}/{$id}"));
    }
}
