<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\BillingStatementStatus;

final readonly class BillingStatement extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?string $currency = null,
        public ?string $customerId = null,
        public ?BillingStatementStatus $status = null,
        public ?string $description = null,
        public ?string $billingStatementUrl = null,
        public ?string $billingDetailsCollection = null,
        public ?string $billingStatementMerchantName = null,
        public ?string $billingStatementNumber = null,
        public ?int $dueAt = null,
        public ?int $finalizedAt = null,
        public ?string $statementDescriptor = null,
        /** @var array<int, array<string, mixed>>|null */
        public ?array $lineItems = null,
        public string|Customer|null $customer = null,
        public string|PaymentIntent|null $paymentIntent = null,
        /** @var array<string, mixed>|null */
        public ?array $paymentSettings = null,
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
            billingStatementUrl: $attributes['billing_statement_url'] ?? null,
            billingDetailsCollection: $attributes['billing_details_collection'] ?? null,
            billingStatementMerchantName: $attributes['billing_statement_merchant_name'] ?? null,
            billingStatementNumber: $attributes['billing_statement_number'] ?? null,
            dueAt: $attributes['due_at'] ?? null,
            finalizedAt: $attributes['finalized_at'] ?? null,
            statementDescriptor: $attributes['statement_descriptor'] ?? null,
            lineItems: $attributes['line_items'] ?? null,
            customer: self::expandRelation($attributes, 'customer', Customer::class),
            paymentIntent: self::expandRelation($attributes, 'payment_intent', PaymentIntent::class),
            paymentSettings: $attributes['payment_settings'] ?? null,
        );
    }
}
