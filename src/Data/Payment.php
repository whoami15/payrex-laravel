<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\PaymentStatus;

final readonly class Payment extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?int $amount = null,
        public readonly ?int $amountRefunded = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $billing = null,
        public readonly ?string $currency = null,
        public readonly ?string $description = null,
        public readonly ?int $fee = null,
        public readonly ?int $netAmount = null,
        public readonly ?string $paymentIntentId = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $paymentMethod = null,
        public readonly ?PaymentStatus $status = null,
        public readonly string|Customer|null $customer = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $pageSession = null,
        public readonly ?bool $refunded = null,
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
            amountRefunded: $attributes['amount_refunded'] ?? null,
            billing: $attributes['billing'] ?? null,
            currency: $attributes['currency'] ?? null,
            description: $attributes['description'] ?? null,
            fee: $attributes['fee'] ?? null,
            netAmount: $attributes['net_amount'] ?? null,
            paymentIntentId: $attributes['payment_intent_id'] ?? null,
            paymentMethod: $attributes['payment_method'] ?? null,
            status: self::castEnum($attributes, 'status', PaymentStatus::class),
            customer: self::expandRelation($attributes, 'customer', Customer::class),
            pageSession: $attributes['page_session'] ?? null,
            refunded: $attributes['refunded'] ?? null,
        );
    }
}
