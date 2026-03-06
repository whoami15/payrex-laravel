<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;

final readonly class BillingStatement extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $customerId = null,
        public readonly ?BillingStatementStatus $status = null,
        public readonly ?string $description = null,
        public readonly ?string $url = null,
        public readonly ?string $billingDetailsCollection = null,
        public readonly ?string $billingStatementMerchantName = null,
        public readonly ?string $billingStatementNumber = null,
        public readonly ?string $billingStatementUrl = null,
        public readonly ?int $dueAt = null,
        public readonly ?int $finalizedAt = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $lineItems = null,
        public readonly string|Customer|null $customer = null,
        public readonly string|PaymentIntent|null $paymentIntent = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $paymentSettings = null,
        public readonly ?string $setupFutureUsage = null,
        public readonly ?string $statementDescriptor = null,
    ) {
        parent::__construct($attributes);
    }

    /**
     * Create a new instance from an array of API attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function from(array $attributes): static
    {
        return new self(
            attributes: $attributes,
            amount: $attributes['amount'] ?? null,
            currency: $attributes['currency'] ?? null,
            customerId: $attributes['customer_id'] ?? null,
            status: self::castEnum($attributes, 'status', BillingStatementStatus::class),
            description: $attributes['description'] ?? null,
            url: $attributes['url'] ?? null,
            billingDetailsCollection: $attributes['billing_details_collection'] ?? null,
            billingStatementMerchantName: $attributes['billing_statement_merchant_name'] ?? null,
            billingStatementNumber: $attributes['billing_statement_number'] ?? null,
            billingStatementUrl: $attributes['billing_statement_url'] ?? null,
            dueAt: $attributes['due_at'] ?? null,
            finalizedAt: $attributes['finalized_at'] ?? null,
            lineItems: $attributes['line_items'] ?? null,
            customer: self::expandRelation($attributes, 'customer', Customer::class),
            paymentIntent: self::expandRelation($attributes, 'payment_intent', PaymentIntent::class),
            paymentSettings: $attributes['payment_settings'] ?? null,
            setupFutureUsage: $attributes['setup_future_usage'] ?? null,
            statementDescriptor: $attributes['statement_descriptor'] ?? null,
        );
    }
}
