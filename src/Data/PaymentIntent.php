<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\PaymentIntentStatus;

final readonly class PaymentIntent extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?int $amount = null,
        public ?int $amountReceived = null,
        public ?int $amountCapturable = null,
        public ?string $clientSecret = null,
        public ?string $currency = null,
        public ?string $description = null,
        /** @var array<string, mixed>|null */
        public ?array $lastPaymentError = null,
        public string|Payment|null $latestPayment = null,
        /** @var array<string, mixed>|null */
        public ?array $nextAction = null,
        /** @var array<string, mixed>|null */
        public ?array $paymentMethodOptions = null,
        /** @var array<int, string>|null */
        public ?array $paymentMethods = null,
        public ?string $statementDescriptor = null,
        public ?PaymentIntentStatus $status = null,
        public ?string $paymentMethodId = null,
        public ?int $captureBeforeAt = null,
        public string|Customer|null $customer = null,
        public ?string $returnUrl = null,
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
            amountReceived: $attributes['amount_received'] ?? null,
            amountCapturable: $attributes['amount_capturable'] ?? null,
            clientSecret: $attributes['client_secret'] ?? null,
            currency: $attributes['currency'] ?? null,
            description: $attributes['description'] ?? null,
            lastPaymentError: $attributes['last_payment_error'] ?? null,
            latestPayment: self::expandRelation($attributes, 'latest_payment', Payment::class),
            nextAction: $attributes['next_action'] ?? null,
            paymentMethodOptions: $attributes['payment_method_options'] ?? null,
            paymentMethods: $attributes['payment_methods'] ?? null,
            statementDescriptor: $attributes['statement_descriptor'] ?? null,
            status: self::castEnum($attributes, 'status', PaymentIntentStatus::class),
            paymentMethodId: $attributes['payment_method_id'] ?? null,
            captureBeforeAt: $attributes['capture_before_at'] ?? null,
            customer: self::expandRelation($attributes, 'customer', Customer::class),
            returnUrl: $attributes['return_url'] ?? null,
        );
    }
}
