<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\PayoutStatus;

final readonly class Payout extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?int $netAmount = null,
        public ?PayoutStatus $status = null,
        /** @var array<string, mixed>|null */
        public ?array $destination = null,
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
            status: self::castEnum($attributes, 'status', PayoutStatus::class),
            destination: $attributes['destination'] ?? null,
        );
    }
}
