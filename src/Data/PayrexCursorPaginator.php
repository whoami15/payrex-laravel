<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

/**
 * Adapts PayRex API responses to Laravel's CursorPaginator.
 *
 * @template-covariant T of PayrexObject
 *
 * @method $this appends(array<string, mixed>|string $key, string|null $value = null)
 * @method $this withQueryString()
 *
 * @extends CursorPaginator<int, T>
 */
final class PayrexCursorPaginator extends CursorPaginator // @phpstan-ignore generics.variance
{
    /**
     * @param  array<int, T>|Collection<int, T>  $items
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        mixed $items,
        int $perPage,
        protected bool $apiHasMore,
        ?Cursor $cursor = null,
        array $options = [],
    ) {
        $this->parameters = ['id'];

        parent::__construct($items, $perPage, $cursor, $options);
    }

    /**
     * Set the items for the paginator, using the API's has_more flag.
     */
    protected function setItems(mixed $items): void
    {
        $this->items = $items instanceof Collection ? $items : new Collection($items);

        $this->hasMore = $this->apiHasMore;

        if (! is_null($this->cursor) && $this->cursor->pointsToPreviousItems()) {
            $this->items = $this->items->reverse()->values();
        }
    }
}
