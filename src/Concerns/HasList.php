<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Concerns;

use LegionHQ\LaravelPayrex\Data\PayrexCollection;
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
            fn (array $pagination) => $this->list(array_merge($params, $pagination)), // @phpstan-ignore argument.type
        );
    }
}
