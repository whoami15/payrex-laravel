<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Concerns;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use LegionHQ\LaravelPayrex\Data\PayrexCollection;
use LegionHQ\LaravelPayrex\Data\PayrexCursorPaginator;
use LegionHQ\LaravelPayrex\Data\PayrexObject;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Resources\ApiResource;

/**
 * @mixin ApiResource
 */
trait HasList
{
    /**
     * Get the DTO class used to hydrate each item in the list.
     *
     * @return class-string<PayrexObject>
     */
    abstract protected function listItemClass(): string;

    /**
     * List resources with optional cursor-based pagination parameters.
     *
     * @param  array{limit?: int, before?: string, after?: string}  $params
     * @return PayrexCollection<PayrexObject>
     *
     * @throws PayrexApiException
     */
    public function list(array $params = []): PayrexCollection
    {
        return new PayrexCollection(
            $this->transport->request('GET', $this->resourceUri(), $params),
            $this->listItemClass(),
        );
    }

    /**
     * List resources with cursor-based pagination, returning a Laravel CursorPaginator.
     *
     * Resolves the cursor from the current request's query string and translates
     * it to the PayRex API's after/before parameters automatically.
     *
     * @param  int  $perPage  Number of items per page.
     * @param  array<string, mixed>  $params  Additional list parameters (e.g., filters like name, email).
     * @param  array<string, mixed>  $options  Paginator options (path, query, fragment, pageName).
     * @return PayrexCursorPaginator<PayrexObject>
     *
     * @throws PayrexApiException
     */
    public function paginate(int $perPage = 10, array $params = [], array $options = []): PayrexCursorPaginator
    {
        $cursor = CursorPaginator::resolveCurrentCursor();

        $params['limit'] = $perPage;

        if ($cursor !== null) {
            if ($cursor->pointsToNextItems()) {
                $params['after'] = $cursor->parameter('id');
            } else {
                $params['before'] = $cursor->parameter('id');
            }
        }

        $options['path'] ??= Paginator::resolveCurrentPath();

        return $this->list($params)->toCursorPaginator($perPage, $cursor, $options);
    }
}
