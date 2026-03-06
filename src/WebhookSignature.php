<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;

/** @internal Used by VerifyWebhookSignature middleware and PayrexClient. */
final class WebhookSignature
{
    /**
     * Verify a webhook signature header against the expected HMAC-SHA256 signature.
     *
     * @throws WebhookVerificationException
     */
    public static function verifyHeader(
        string $payload,
        string $signatureHeader,
        string $secret,
        int $tolerance = 300,
    ): void {
        if ($secret === '') {
            throw WebhookVerificationException::emptySecret();
        }

        $parsed = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $segments = explode('=', trim($part), 2);
            if (count($segments) === 2) {
                $parsed[$segments[0]] = $segments[1];
            }
        }

        $timestamp = $parsed['t'] ?? throw WebhookVerificationException::invalidHeader();

        if (! ctype_digit($timestamp)) {
            throw WebhookVerificationException::invalidHeader();
        }

        $testSignature = $parsed['te'] ?? '';
        $liveSignature = $parsed['li'] ?? '';

        $signatures = array_filter([$testSignature, $liveSignature], fn (string $sig): bool => $sig !== '');

        if ($signatures === []) {
            throw WebhookVerificationException::invalidHeader();
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        $verified = false;

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                $verified = true;
                break;
            }
        }

        if (! $verified) {
            throw WebhookVerificationException::invalidSignature();
        }

        if ($tolerance > 0 && (time() - (int) $timestamp) > $tolerance) {
            throw WebhookVerificationException::timestampOutsideTolerance();
        }
    }
}
