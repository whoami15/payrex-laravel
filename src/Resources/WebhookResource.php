<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Concerns\HasList;
use LegionHQ\LaravelPayrex\Data\DeletedResource;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Data\PayrexCursorPaginator;
use LegionHQ\LaravelPayrex\Data\WebhookEndpoint;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

/**
 * @method PayrexCollection<WebhookEndpoint> list(array{limit?: int, before?: string, after?: string, url?: string, description?: string} $params = [])
 * @method PayrexCursorPaginator<WebhookEndpoint> paginate(int $perPage = 10, array<string, mixed> $params = [], array<string, mixed> $options = [])
 */
final class WebhookResource extends ApiResource
{
    use HasList;

    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/webhooks';
    }

    /**
     * Get the DTO class used to hydrate each item in the list.
     */
    protected function listItemClass(): string
    {
        return WebhookEndpoint::class;
    }

    /**
     * Create a new webhook endpoint.
     *
     * @param  array{
     *     url: string,
     *     events: array<string>,
     *     description?: string,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params, ?string $idempotencyKey = null): WebhookEndpoint
    {
        return WebhookEndpoint::from($this->transport->request('POST', $this->resourceUri(), $params, $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Retrieve a webhook endpoint by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): WebhookEndpoint
    {
        return WebhookEndpoint::from($this->transport->request('GET', "{$this->resourceUri()}/{$id}"));
    }

    /**
     * Update a webhook endpoint.
     *
     * @param  array{
     *     url?: string,
     *     events?: array<string>,
     *     description?: string,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): WebhookEndpoint
    {
        return WebhookEndpoint::from($this->transport->request('PUT', "{$this->resourceUri()}/{$id}", $params));
    }

    /**
     * Delete a webhook endpoint.
     *
     * @throws PayrexApiException
     */
    public function delete(string $id): DeletedResource
    {
        return DeletedResource::from($this->transport->request('DELETE', "{$this->resourceUri()}/{$id}"));
    }

    /**
     * Enable a webhook endpoint.
     *
     * @throws PayrexApiException
     */
    public function enable(string $id, ?string $idempotencyKey = null): WebhookEndpoint
    {
        return WebhookEndpoint::from($this->transport->request('POST', "{$this->resourceUri()}/{$id}/enable", [], $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Disable a webhook endpoint.
     *
     * @throws PayrexApiException
     */
    public function disable(string $id, ?string $idempotencyKey = null): WebhookEndpoint
    {
        return WebhookEndpoint::from($this->transport->request('POST', "{$this->resourceUri()}/{$id}/disable", [], $this->idempotencyHeader($idempotencyKey)));
    }
}
