<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use LegionHQ\LaravelPayrex\Data\ApiResponseMetadata;
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
use Psr\Http\Message\RequestInterface;
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

    protected ?ApiResponseMetadata $lastResponseMetadata = null;

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
        if ($secretKey === '') {
            throw new InvalidArgumentException('PayRex secret key cannot be empty.');
        }

        if ($baseUrl === '') {
            throw new InvalidArgumentException('PayRex API base URL cannot be empty.');
        }

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
     * Get metadata from the most recent API response.
     *
     * Returns headers, status code, and a case-insensitive header() helper.
     * Useful for inspecting rate limit headers, request IDs, etc.
     */
    public function getLastResponse(): ?ApiResponseMetadata
    {
        return $this->lastResponseMetadata;
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
            ->connectTimeout($this->connectTimeout)
            ->withRequestMiddleware(self::normalizeFormEncoding());

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
     * Create a Guzzle request middleware that replaces numeric array indices
     * in form-encoded bodies with empty brackets.
     *
     * PHP's http_build_query encodes arrays as field[0]=a&field[1]=b, but
     * the PayRex API expects field[]=a&field[]=b.
     *
     * @internal
     */
    protected static function normalizeFormEncoding(): Closure
    {
        return static function (RequestInterface $request): RequestInterface {
            $body = (string) $request->getBody();

            if ($body === '') {
                return $request;
            }

            $normalized = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $body) ?? $body;

            return $request->withBody(Utils::streamFor($normalized));
        };
    }

    /**
     * Send a GET request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function get(string $uri, array $query = [], array $headers = []): array
    {
        return $this->handleResponse($this->newRequest()->withHeaders($headers)->get($uri, $query));
    }

    /**
     * Send a POST request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function post(string $uri, array $data = [], array $headers = []): array
    {
        return $this->handleResponse($this->newRequest()->withHeaders($headers)->post($uri, $data));
    }

    /**
     * Send a PUT request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function put(string $uri, array $data = [], array $headers = []): array
    {
        return $this->handleResponse($this->newRequest()->withHeaders($headers)->put($uri, $data));
    }

    /**
     * Send a DELETE request to the API.
     *
     * @internal Used by resource classes. Not part of the public API.
     *
     * @param  array<string, mixed>  $query
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function delete(string $uri, array $query = [], array $headers = []): array
    {
        return $this->handleResponse($this->newRequest()->withHeaders($headers)->delete($uri, $query));
    }

    /**
     * Handle the API response and throw typed exceptions for error status codes.
     *
     * @return array<string, mixed>
     */
    protected function handleResponse(Response $response): array
    {
        $this->lastResponseMetadata = new ApiResponseMetadata(
            headers: array_map(
                fn (array $values): string => $values[0] ?? '',
                $response->headers(),
            ),
            statusCode: $response->status(),
        );

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
