<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\PayrexClient;

abstract class ApiResource
{
    /**
     * Create a new API resource instance.
     */
    public function __construct(
        protected readonly PayrexClient $client,
    ) {}

    /**
     * Get the base URI for this resource.
     */
    abstract protected function resourceUri(): string;

    /**
     * Merge the client's default currency into the given parameters.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function withDefaultCurrency(array $params): array
    {
        $params['currency'] ??= $this->client->defaultCurrency();

        return $params;
    }

    /**
     * Build an Idempotency-Key header array from an optional key.
     *
     * PayRex doesn't support this header yet, but we send it anyway
     * so it just works when they do.
     *
     * @return array<string, string>
     */
    protected function idempotencyHeader(?string $idempotencyKey): array
    {
        return $idempotencyKey !== null ? ['Idempotency-Key' => $idempotencyKey] : [];
    }
}
