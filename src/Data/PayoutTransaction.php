<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

final readonly class PayoutTransaction extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?int $netAmount = null,
        public ?string $transactionId = null,
        public ?string $transactionType = null,
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
            netAmount: $attributes['net_amount'] ?? null,
            transactionId: $attributes['transaction_id'] ?? null,
            transactionType: $attributes['transaction_type'] ?? null,
        );
    }
}
