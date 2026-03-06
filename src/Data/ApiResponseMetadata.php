<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Data;

final readonly class ApiResponseMetadata
{
    /** @var array<string, string> */
    public array $headers;

    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        array $headers,
        public int $statusCode,
    ) {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }

        $this->headers = $normalized;
    }

    /**
     * Get a single header value by name (case-insensitive).
     */
    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
