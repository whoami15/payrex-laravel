<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;

final readonly class WebhookEndpoint extends PayrexObject
{
    public function __construct(
        array $attributes,
        public ?string $secretKey = null,
        public ?string $url = null,
        /** @var array<int, string>|null */
        public ?array $events = null,
        public ?string $description = null,
        public ?WebhookEndpointStatus $status = null,
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
            secretKey: $attributes['secret_key'] ?? null,
            url: $attributes['url'] ?? null,
            events: $attributes['events'] ?? null,
            description: $attributes['description'] ?? null,
            status: self::castEnum($attributes, 'status', WebhookEndpointStatus::class),
        );
    }
}
