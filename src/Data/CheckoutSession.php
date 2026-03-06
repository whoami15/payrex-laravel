<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\CheckoutSessionStatus;

final readonly class CheckoutSession extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?string $clientSecret = null,
        public ?string $currency = null,
        public ?string $customerReferenceId = null,
        public ?string $description = null,
        public ?CheckoutSessionStatus $status = null,
        public ?string $url = null,
        /** @var array<int, array<string, mixed>>|null */
        public ?array $lineItems = null,
        public ?string $successUrl = null,
        public ?string $cancelUrl = null,
        public string|PaymentIntent|null $paymentIntent = null,
        /** @var array<int, string>|null */
        public ?array $paymentMethods = null,
        /** @var array<string, mixed>|null */
        public ?array $paymentMethodOptions = null,
        public ?string $billingDetailsCollection = null,
        public ?string $submitType = null,
        public ?string $statementDescriptor = null,
        public ?int $expiresAt = null,
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
