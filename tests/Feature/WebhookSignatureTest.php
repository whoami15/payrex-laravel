<?php

declare(strict_types=1);

use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;
use LegionHQ\LaravelPayrex\WebhookSignature;

it('verifies a valid test signature', function () {
    $payload = '{"type":"test"}';
    $header = buildWebhookSignature($payload, 'whsec_test');

    WebhookSignature::verifyHeader($payload, $header, 'whsec_test');
})->throwsNoExceptions();

it('verifies a valid live signature', function () {
    $payload = '{"type":"live"}';
    $header = buildWebhookSignature($payload, 'whsec_live', useLive: true);

    WebhookSignature::verifyHeader($payload, $header, 'whsec_live');
})->throwsNoExceptions();

it('throws on invalid header format', function () {
    WebhookSignature::verifyHeader('{}', 'invalid-header-no-timestamp', 'whsec_test');
})->throws(WebhookVerificationException::class, 'Unable to parse Payrex-Signature header.');

it('throws on tampered signature', function () {
    $timestamp = time();
    $header = "t={$timestamp},te=tampered,li=";

    WebhookSignature::verifyHeader('{"type":"test"}', $header, 'whsec_test');
})->throws(WebhookVerificationException::class, 'Webhook signature does not match the expected signature.');

it('throws on expired timestamp', function () {
    $payload = '{"type":"test"}';
    $expiredTimestamp = time() - 600;
    $header = buildWebhookSignature($payload, 'whsec_test', $expiredTimestamp);

    WebhookSignature::verifyHeader($payload, $header, 'whsec_test', tolerance: 300);
})->throws(WebhookVerificationException::class, 'Webhook timestamp is outside the tolerance zone.');

it('throws when both signatures are empty', function () {
    $timestamp = time();
    $header = "t={$timestamp},te=,li=";

    WebhookSignature::verifyHeader('{"type":"test"}', $header, 'whsec_test');
})->throws(WebhookVerificationException::class, 'Unable to parse Payrex-Signature header.');

it('skips timestamp check when tolerance is zero', function () {
    $payload = '{"type":"test"}';
    $oldTimestamp = 1000000;
    $header = buildWebhookSignature($payload, 'whsec_test', $oldTimestamp);

    WebhookSignature::verifyHeader($payload, $header, 'whsec_test', tolerance: 0);
})->throwsNoExceptions();

it('throws when secret is empty', function () {
    $payload = '{"type":"test"}';
    $header = 't=123,te=abc,li=';

    WebhookSignature::verifyHeader($payload, $header, '');
})->throws(WebhookVerificationException::class, 'Webhook secret is not configured.');

it('throws when timestamp is non-numeric', function () {
    $header = 't=abc,te=somesig,li=';

    WebhookSignature::verifyHeader('{}', $header, 'whsec_test');
})->throws(WebhookVerificationException::class, 'Unable to parse Payrex-Signature header.');

it('parses header without spaces after commas', function () {
    $payload = '{"type":"test"}';
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');
    $header = "t={$timestamp},te={$signature},li=";

    WebhookSignature::verifyHeader($payload, $header, 'whsec_test');
})->throwsNoExceptions();

it('verifies when both test and live signatures are present and test matches', function () {
    $payload = '{"type":"test"}';
    $timestamp = time();
    $testSignature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');
    $header = "t={$timestamp},te={$testSignature},li=nonmatching_live_sig";

    WebhookSignature::verifyHeader($payload, $header, 'whsec_test');
})->throwsNoExceptions();

it('verifies when both test and live signatures are present and live matches', function () {
    $payload = '{"type":"live"}';
    $timestamp = time();
    $liveSignature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_live');
    $header = "t={$timestamp},te=nonmatching_test_sig,li={$liveSignature}";

    WebhookSignature::verifyHeader($payload, $header, 'whsec_live');
})->throwsNoExceptions();

it('rejects when both signatures are present but neither matches', function () {
    $timestamp = time();
    $header = "t={$timestamp},te=wrong_test_sig,li=wrong_live_sig";

    WebhookSignature::verifyHeader('{"type":"test"}', $header, 'whsec_test');
})->throws(WebhookVerificationException::class, 'Webhook signature does not match the expected signature.');
