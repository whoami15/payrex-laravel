<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\RefundReason;
use LegionHQ\LaravelPayrex\Enums\RefundStatus;

final readonly class Refund extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?string $currency = null,
        public ?RefundStatus $status = null,
        public ?string $description = null,
        public ?RefundReason $reason = null,
        public ?string $remarks = null,
        public ?string $paymentId = null,
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
