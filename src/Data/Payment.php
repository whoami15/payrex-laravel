<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\PaymentStatus;

final readonly class Payment extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?int $amountRefunded = null,
        /** @var array<string, mixed>|null */
        public ?array $billing = null,
        public ?string $currency = null,
        public ?string $description = null,
        public ?int $fee = null,
        public ?int $netAmount = null,
        public ?string $paymentIntentId = null,
        /** @var array<string, mixed>|null */
        public ?array $paymentMethod = null,
        public ?PaymentStatus $status = null,
        public string|Customer|null $customer = null,
        /** @var array<string, mixed>|null */
        public ?array $pageSession = null,
        public ?bool $refunded = null,
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
