<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @implements ArrayAccess<string, mixed>
 */
readonly class PayrexObject implements ArrayAccess, JsonSerializable
{
    /** @var array<string, class-string<PayrexObject>> */
    protected const RESOURCE_MAP = [
        'payment_intent' => PaymentIntent::class,
        'payment' => Payment::class,
        'checkout_session' => CheckoutSession::class,
        'refund' => Refund::class,
        'customer' => Customer::class,
        'billing_statement' => BillingStatement::class,
        'billing_statement_line_item' => BillingStatementLineItem::class,
        'payout' => Payout::class,
        'payout_transaction' => PayoutTransaction::class,
        'webhook' => WebhookEndpoint::class,
    ];

    public readonly string $id;

    public readonly string $resource;

    public readonly bool $livemode;

    /** @var array<string, string>|null */
    public readonly ?array $metadata;

    public readonly ?int $createdAt;

    public readonly ?int $updatedAt;

    /**
     * @internal Used by PayrexEvent::data() for polymorphic DTO construction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function constructFrom(array $attributes): self
    {
        $class = static::RESOURCE_MAP[$attributes['resource'] ?? ''] ?? static::class;

        return $class::from($attributes);
    }

    /**
     * Create a new instance from an array of API attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function from(array $attributes): static
    {
        return new static($attributes); // @phpstan-ignore new.static
    }

    /**
     * @template TEnum of \BackedEnum
     *
     * @param  array<string, mixed>  $attributes
     * @param  class-string<TEnum>  $enumClass
     * @return TEnum|null
     */
    protected static function castEnum(array $attributes, string $key, string $enumClass): ?\BackedEnum
    {
        return isset($attributes[$key]) ? $enumClass::tryFrom($attributes[$key]) : null;
    }

    /**
     * @template TRelation of PayrexObject
     *
     * @param  array<string, mixed>  $attributes
     * @param  class-string<TRelation>  $class
     * @return string|TRelation|null
     */
    protected static function expandRelation(array $attributes, string $key, string $class): string|self|null
    {
        return match (true) {
            is_array($attributes[$key] ?? null) => $class::from($attributes[$key]),
            is_string($attributes[$key] ?? null) => $attributes[$key],
            default => null,
        };
    }

    /**
     * Create a new PayRex object instance.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        protected readonly array $attributes,
    ) {
        $this->id = $this->attributes['id'] ?? throw new InvalidArgumentException('Missing required field: id');
        $this->resource = $this->attributes['resource'] ?? '';
        $this->livemode = $this->attributes['livemode'] ?? false;
        $this->metadata = $this->attributes['metadata'] ?? null;
        $this->createdAt = $this->attributes['created_at'] ?? null;
        $this->updatedAt = $this->attributes['updated_at'] ?? null;
    }

    /**
     * Get the object as a plain array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get the JSON-serializable representation of the object.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    /**
     * Determine if the given offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value at the given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Prevent setting values on the immutable object.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('PayrexObject is immutable.');
    }

    /**
     * Prevent unsetting values on the immutable object.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('PayrexObject is immutable.');
    }
}
