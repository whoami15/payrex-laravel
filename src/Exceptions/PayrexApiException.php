<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Exceptions;

class PayrexApiException extends PayrexException
{
    /**
     * @param  array<int, array<string, mixed>>  $errors
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        string $message,
        public readonly array $errors,
        public readonly int $statusCode,
        public readonly array $body,
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromResponse(array $body, int $statusCode): static
    {
        $errors = $body['errors'] ?? [];
        $message = ! empty($errors)
            ? $errors[0]['detail'] ?? 'An API error occurred.'
            : 'An API error occurred.';

        return new static( // @phpstan-ignore new.static
            message: $message,
            errors: $errors,
            statusCode: $statusCode,
            body: $body,
        );
    }
}
