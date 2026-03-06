<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use InvalidArgumentException;
use LegionHQ\LaravelPayrex\Data\ApiResponseMetadata;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
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

final class PayrexClient
{
    protected readonly PayrexTransport $transport;

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
        string $secretKey,
        string $baseUrl = 'https://api.payrexhq.com',
        int $timeout = 30,
        int $connectTimeout = 30,
        int $retries = 0,
        int $retryDelay = 100,
        protected readonly string $currency = 'PHP',
        protected readonly string $webhookSecret = '',
    ) {
        if ($secretKey === '') {
            throw new InvalidArgumentException('PayRex secret key cannot be empty.');
        }

        if ($baseUrl === '') {
            throw new InvalidArgumentException('PayRex API base URL cannot be empty.');
        }

        $this->transport = new PayrexTransport($secretKey, $baseUrl, $timeout, $connectTimeout, $retries, $retryDelay);

        $this->paymentIntents = new PaymentIntentResource($this->transport, $this->currency);
        $this->payments = new PaymentResource($this->transport, $this->currency);
        $this->refunds = new RefundResource($this->transport, $this->currency);
        $this->customers = new CustomerResource($this->transport, $this->currency);
        $this->checkoutSessions = new CheckoutSessionResource($this->transport, $this->currency);
        $this->webhooks = new WebhookResource($this->transport, $this->currency);
        $this->billingStatements = new BillingStatementResource($this->transport, $this->currency);
        $this->billingStatementLineItems = new BillingStatementLineItemResource($this->transport, $this->currency);
        $this->payoutTransactions = new PayoutTransactionResource($this->transport, $this->currency);
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
        return $this->transport->getLastResponse();
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
}
