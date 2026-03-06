<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\CheckoutSessionStatus;

final readonly class CheckoutSession extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?string $clientSecret = null,
        public readonly ?string $currency = null,
        public readonly ?string $customerReferenceId = null,
        public readonly ?string $description = null,
        public readonly ?CheckoutSessionStatus $status = null,
        public readonly ?string $url = null,
        /** @var array<int, array<string, mixed>>|null */
        public readonly ?array $lineItems = null,
        public readonly ?string $successUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly string|PaymentIntent|null $paymentIntent = null,
        /** @var array<int, string>|null */
        public readonly ?array $paymentMethods = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $paymentMethodOptions = null,
        public readonly ?string $billingDetailsCollection = null,
        public readonly ?string $submitType = null,
        public readonly ?string $statementDescriptor = null,
        public readonly ?int $expiresAt = null,
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
            clientSecret: $attributes['client_secret'] ?? null,
            currency: $attributes['currency'] ?? null,
            customerReferenceId: $attributes['customer_reference_id'] ?? null,
            description: $attributes['description'] ?? null,
            status: self::castEnum($attributes, 'status', CheckoutSessionStatus::class),
            url: $attributes['url'] ?? null,
            lineItems: $attributes['line_items'] ?? null,
            successUrl: $attributes['success_url'] ?? null,
            cancelUrl: $attributes['cancel_url'] ?? null,
            paymentIntent: self::expandRelation($attributes, 'payment_intent', PaymentIntent::class),
            paymentMethods: $attributes['payment_methods'] ?? null,
            paymentMethodOptions: $attributes['payment_method_options'] ?? null,
            billingDetailsCollection: $attributes['billing_details_collection'] ?? null,
            submitType: $attributes['submit_type'] ?? null,
            statementDescriptor: $attributes['statement_descriptor'] ?? null,
            expiresAt: $attributes['expires_at'] ?? null,
        );
    }
}
