<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;

final readonly class Refund extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?string $currency = null,
        public readonly ?RefundStatus $status = null,
        public readonly ?string $description = null,
        public readonly ?RefundReason $reason = null,
        public readonly ?string $remarks = null,
        public readonly ?string $paymentId = null,
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
            status: self::castEnum($attributes, 'status', RefundStatus::class),
            description: $attributes['description'] ?? null,
            reason: self::castEnum($attributes, 'reason', RefundReason::class),
            remarks: $attributes['remarks'] ?? null,
            paymentId: $attributes['payment_id'] ?? null,
        );
    }
}
