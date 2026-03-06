<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Support\LazyCollection;
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
     * @param  (Closure(array<string, string>): PayrexCollection<T>)|null  $paginator
     */
    public function __construct(
        protected array $attributes,
        protected string $itemClass,
        protected ?Closure $paginator = null,
    ) {
        $this->resource = $attributes['resource'] ?? null;
        $this->hasMore = $attributes['has_more'] ?? false;
        $this->data = array_map(
            fn (array $item) => ($this->itemClass)::from($item),
            $attributes['data'] ?? [],
        );
    }

    /**
     * Lazily iterate through all pages of results.
     *
     * @param  int  $maxPages  Safety limit to prevent infinite loops (0 = unlimited).
     * @return LazyCollection<int, T>
     */
    public function autoPaginate(int $maxPages = 100): LazyCollection // @phpstan-ignore generics.variance
    {
        return new LazyCollection(function () use ($maxPages) {
            $collection = $this;
            $page = 0;

            while (true) {
                foreach ($collection->data as $item) {
                    yield $item;
                }

                $page++;

                if (! $collection->hasMore || empty($collection->data) || $collection->paginator === null) {
                    break;
                }

                if ($maxPages > 0 && $page >= $maxPages) {
                    break;
                }

                $data = $collection->data;
                $lastItem = end($data);
                $collection = ($collection->paginator)(['after' => $lastItem->id]);
            }
        });
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
