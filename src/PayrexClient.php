<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
use LegionHQ\LaravelPayrex\Exceptions\AuthenticationException;
use LegionHQ\LaravelPayrex\Exceptions\InvalidRequestException;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Exceptions\RateLimitException;
use LegionHQ\LaravelPayrex\Exceptions\ResourceNotFoundException;
use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;
use LegionHQ\LaravelPayrex\Resources\BillingStatementLineItemResource;
use LegionHQ\LaravelPayrex\Resources\BillingStatementResource;
use LegionHQ\LaravelPayrex\Resources\CheckoutSessionResource;
use LegionHQ\LaravelPayrex\Resources\CustomerResource;
use LegionHQ\LaravelPayrex\Resources\PaymentIntentResource;
use LegionHQ\LaravelPayrex\Resources\PaymentResource;
use LegionHQ\LaravelPayrex\Resources\PayoutTransactionResource;
use LegionHQ\LaravelPayrex\Resources\RefundResource;
use LegionHQ\LaravelPayrex\Resources\WebhookResource;
use Throwable;

final class PayrexClient
{
    protected readonly PaymentIntentResource $paymentIntents;

    protected readonly PaymentResource $payments;

    protected readonly RefundResource $refunds;

    protected readonly CustomerResource $customers;

    protected readonly CheckoutSessionResource $checkoutSessions;

    protected readonly WebhookResource $webhooks;

    protected readonly BillingStatementResource $billingStatements;

    protected readonly BillingStatementLineItemResource $billingStatementLineItems;

    protected readonly PayoutTransactionResource $payoutTransactions;

    public function __construct(
        protected readonly string $secretKey,
        protected readonly string $baseUrl = 'https://api.payrexhq.com',
        protected readonly int $timeout = 30,
        protected readonly int $connectTimeout = 30,
        protected readonly int $retries = 0,
        protected readonly int $retryDelay = 100,
        protected readonly string $currency = 'PHP',
        protected readonly string $webhookSecret = '',
    ) {
        $this->paymentIntents = new PaymentIntentResource($this);
        $this->payments = new PaymentResource($this);
        $this->refunds = new RefundResource($this);
        $this->customers = new CustomerResource($this);
        $this->checkoutSessions = new CheckoutSessionResource($this);
        $this->webhooks = new WebhookResource($this);
        $this->billingStatements = new BillingStatementResource($this);
        $this->billingStatementLineItems = new BillingStatementLineItemResource($this);
        $this->payoutTransactions = new PayoutTransactionResource($this);
    }

    /**
     * Get the payment intents resource.
     */
    public function paymentIntents(): PaymentIntentResource
    {
        return $this->paymentIntents;
    }

    /**
     * Get the payments resource.
     */
    public function payments(): PaymentResource
    {
        return $this->payments;
    }

    /**
     * Get the refunds resource.
     */
    public function refunds(): RefundResource
    {
        return $this->refunds;
    }

    /**
     * Get the customers resource.
     */
    public function customers(): CustomerResource
    {
        return $this->customers;
    }

    /**
     * Get the checkout sessions resource.
     */
    public function checkoutSessions(): CheckoutSessionResource
    {
        return $this->checkoutSessions;
    }

    /**
     * Get the webhooks resource.
     */
    public function webhooks(): WebhookResource
    {
        return $this->webhooks;
    }

    /**
     * Get the billing statements resource.
     */
    public function billingStatements(): BillingStatementResource
    {
        return $this->billingStatements;
    }

    /**
     * Get the billing statement line items resource.
     */
    public function billingStatementLineItems(): BillingStatementLineItemResource
    {
        return $this->billingStatementLineItems;
    }

    /**
     * Get the payout transactions resource.
     */
    public function payoutTransactions(): PayoutTransactionResource
    {
        return $this->payoutTransactions;
    }

    /**
     * Get the default currency.
     */
    public function defaultCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Verify a webhook signature and construct a typed event from the payload.
     *
     * When $secret is null, the configured webhook secret is used.
     *
     * @throws WebhookVerificationException
     */
    public function constructEvent(
        string $payload,
        string $signatureHeader,
        ?string $secret = null,
        int $tolerance = 300,
    ): PayrexEvent {
        WebhookSignature::verifyHeader($payload, $signatureHeader, $secret ?? $this->webhookSecret, $tolerance);

        $data = json_decode($payload, true);

        if (! is_array($data)) {
            throw WebhookVerificationException::invalidPayload();
        }

        return PayrexEvent::constructFrom($data);
    }

    /**
     * Build a new HTTP request with authentication and encoding.
     */
    protected function newRequest(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->secretKey, '')
            ->asForm()
            ->acceptJson()
            ->withUserAgent('laravel-payrex php/'.PHP_VERSION)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout);

        if ($this->retries > 0) {
            $request->retry(
                $this->retries,
                $this->retryDelay,
                fn (Throwable $exception, PendingRequest $_pendingRequest): bool => $exception instanceof RequestException
                    && $exception->response->serverError(),
                throw: false,
            );
        }

        return $request;
    }

    /**
     * Send a GET request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->handleResponse($this->newRequest()->get($uri, $query));
    }

    /**
     * Send a POST request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->handleResponse($this->newRequest()->post($uri, $data));
    }

    /**
     * Send a PUT request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function put(string $uri, array $data = []): array
    {
        return $this->handleResponse($this->newRequest()->put($uri, $data));
    }

    /**
     * Send a DELETE request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function delete(string $uri, array $query = []): array
    {
        return $this->handleResponse($this->newRequest()->delete($uri, $query));
    }

    /**
     * Handle the API response and throw typed exceptions for error status codes.
     *
     * @return array<string, mixed>
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $body = $response->json() ?? [];

        $status = $response->status();

        throw match ($status) {
            400 => InvalidRequestException::fromResponse($body, $status),
            401 => AuthenticationException::fromResponse($body, $status),
            404 => ResourceNotFoundException::fromResponse($body, $status),
            429 => RateLimitException::fromResponse($body, $status),
            default => PayrexApiException::fromResponse($body, $status),
        };
    }
}
