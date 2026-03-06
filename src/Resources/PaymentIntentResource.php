<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Resources;

use LegionHQ\LaravelPayrex\Data\PaymentIntent;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;

final class PaymentIntentResource extends ApiResource
{
    /**
     * Get the base URI for this resource.
     */
    protected function resourceUri(): string
    {
        return '/payment_intents';
    }

    /**
     * Create a new payment intent.
     *
     * @param  array{
     *     amount: int,
     *     currency?: string,
     *     payment_methods?: array<string>,
     *     description?: string,
     *     statement_descriptor?: string,
     *     metadata?: array<string, string>,
     *     payment_method_options?: array{
     *         card?: array{
     *             capture_type?: string,
     *             allowed_bins?: array<string>,
     *             allowed_funding?: array<string>,
     *         },
     *     },
     * }  $params
     *
     * @throws PayrexApiException
     */
    public function create(array $params, ?string $idempotencyKey = null): PaymentIntent
    {
        return PaymentIntent::from($this->client->post($this->resourceUri(), $this->withDefaultCurrency($params), $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Retrieve a payment intent by ID.
     *
     * @throws PayrexApiException
     */
    public function retrieve(string $id): PaymentIntent
    {
        return PaymentIntent::from($this->client->get("{$this->resourceUri()}/{$id}"));
    }

    /**
     * Cancel a payment intent.
     *
     * @throws PayrexApiException
     */
    public function cancel(string $id, ?string $idempotencyKey = null): PaymentIntent
    {
        return PaymentIntent::from($this->client->post("{$this->resourceUri()}/{$id}/cancel", [], $this->idempotencyHeader($idempotencyKey)));
    }

    /**
     * Capture an authorized payment intent.
     *
     * @param  array{amount: int}  $params
     *
     * @throws PayrexApiException
     */
    public function capture(string $id, array $params, ?string $idempotencyKey = null): PaymentIntent
    {
        return PaymentIntent::from($this->client->post("{$this->resourceUri()}/{$id}/capture", $params, $this->idempotencyHeader($idempotencyKey)));
    }
}
