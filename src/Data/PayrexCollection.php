<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Illuminate\Pagination\Cursor;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use Traversable;

/**
 * @template-covariant T of PayrexObject
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<int, T>
 */
final readonly class PayrexCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    public ?string $resource;

    public bool $hasMore;

    /** @var array<int, T> */
    public array $data;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  class-string<T>  $itemClass
     */
    public function __construct(
        protected array $attributes,
        protected string $itemClass,
    ) {
        $this->resource = $attributes['resource'] ?? null;
        $this->hasMore = $attributes['has_more'] ?? false;
        $this->data = array_map(
            fn (array $item) => ($this->itemClass)::from($item),
            $attributes['data'] ?? [],
        );
    }

    /**
     * Convert the collection to a Laravel CursorPaginator.
     *
     * @internal
     *
     * @param  int|null  $perPage  Items per page (defaults to the current page size, minimum 1).
     * @param  Cursor|null  $cursor  Current cursor for previous/next page determination.
     * @param  array<string, mixed>  $options  Additional paginator options (path, query, fragment, pageName).
     * @return PayrexCursorPaginator<T>
     */
    public function toCursorPaginator(?int $perPage = null, ?Cursor $cursor = null, array $options = []): PayrexCursorPaginator
    {
        return new PayrexCursorPaginator(
            items: $this->data,
            perPage: $perPage ?? max($this->count(), 1),
            apiHasMore: $this->hasMore,
            cursor: $cursor,
            options: $options,
        );
    }

    /**
     * Get the number of items in the current page.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get an iterator for the items in the current page.
     *
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Get the JSON-serializable representation of the collection.
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
        if ($offset === 'data') {
            return $this->data;
        }

        return $this->attributes[$offset] ?? null;
    }

    /**
     * Prevent setting values on the immutable collection.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('PayrexCollection is immutable.');
    }

    /**
     * Prevent unsetting values on the immutable collection.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('PayrexCollection is immutable.');
    }
}
