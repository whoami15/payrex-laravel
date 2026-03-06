<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

final readonly class BillingStatementLineItem extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?string $description = null,
        public readonly ?int $unitPrice = null,
        public readonly ?int $quantity = null,
        public readonly ?string $billingStatementId = null,
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
            description: $attributes['description'] ?? null,
            unitPrice: $attributes['unit_price'] ?? null,
            quantity: $attributes['quantity'] ?? null,
            billingStatementId: $attributes['billing_statement_id'] ?? null,
        );
    }
}
