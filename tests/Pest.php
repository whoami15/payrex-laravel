<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function loadFixture(string $path): array
{
    return json_decode(file_get_contents(__DIR__.'/fixtures/'.$path), true, 512, JSON_THROW_ON_ERROR);
}

function buildWebhookSignature(string $payload, string $secret, ?int $timestamp = null, bool $useLive = false): string
{
    $timestamp = $timestamp ?? time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    if ($useLive) {
        return "t={$timestamp},te=,li={$signature}";
    }

    return "t={$timestamp},te={$signature},li=";
}
