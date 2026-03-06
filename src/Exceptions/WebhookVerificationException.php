<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Exceptions;

final class WebhookVerificationException extends PayrexException
{
    public static function missingHeader(): self
    {
        return new self('Missing Payrex-Signature header.');
    }

    public static function invalidHeader(): self
    {
        return new self('Unable to parse Payrex-Signature header.');
    }

    public static function timestampOutsideTolerance(): self
    {
        return new self('Webhook timestamp is outside the tolerance zone.');
    }

    public static function invalidSignature(): self
    {
        return new self('Webhook signature does not match the expected signature.');
    }

    public static function emptySecret(): self
    {
        return new self('Webhook secret is not configured.');
    }

    public static function invalidPayload(): self
    {
        return new self('Invalid JSON payload.');
    }
}
