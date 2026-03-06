<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

final readonly class DeletedResource extends PayrexObject
{
    public function __construct(
        array $attributes,
        public bool $deleted = true,
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
            deleted: $attributes['deleted'] ?? true,
        );
    }
}
