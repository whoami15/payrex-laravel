<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Data\Payment;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

final class PaymentResource extends ApiResource
{
    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/payments';
    }

    /**
     * Retrieve a payment by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): Payment
    {
        return Payment::from($this->transport->request('GET', "{$this->resourceUri()}/{$id}"));
    }

    /**
     * Update a payment.
     *
     * @param  array{
     *     description?: string,
     *     metadata?: array<string, string>,
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function update(string $id, array $params): Payment
    {
        return Payment::from($this->transport->request('PUT', "{$this->resourceUri()}/{$id}", $params));
    }
}
