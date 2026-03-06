<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

final readonly class Customer extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $currency = null,
        public readonly ?string $billingStatementPrefix = null,
        public readonly ?string $nextBillingStatementSequenceNumber = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $billing = null,
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
            name: $attributes['name'] ?? null,
            email: $attributes['email'] ?? null,
            currency: $attributes['currency'] ?? null,
            billingStatementPrefix: $attributes['billing_statement_prefix'] ?? null,
            nextBillingStatementSequenceNumber: $attributes['next_billing_statement_sequence_number'] ?? null,
            billing: $attributes['billing'] ?? null,
        );
    }
}
