<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

use LegionHQ\LaravelPayrex\Enums\WebhookEndpointStatus;

final readonly class WebhookEndpoint extends PayrexObject
{
    public function __construct(
        array $attributes,
        public readonly ?string $secretKey = null,
        public readonly ?string $url = null,
        /** @var array<int, string>|null */
        public readonly ?array $events = null,
        public readonly ?string $description = null,
        public readonly ?WebhookEndpointStatus $status = null,
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
