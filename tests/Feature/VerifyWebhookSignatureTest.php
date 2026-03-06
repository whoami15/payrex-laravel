<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use LegionHQ\LaravelPayrex\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

function buildSignatureHeader(string $payload, string $secret, ?int $timestamp = null, bool $useLive = false): string
{
    $timestamp = $timestamp ?? time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    if ($useLive) {
        return "t={$timestamp},te=,li={$signature}";
    }

    return "t={$timestamp},te={$signature},li=";
}

it('rejects signature with fewer than 3 parts', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test');
    $payload = '{"type":"test"}';

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => 't=123,te=abc',
    ], $payload);

    $middleware->handle($request, fn () => new Response('OK'));
})->throws(AccessDeniedHttpException::class);

it('rejects a tampered signature', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test');
    $payload = '{"type":"test"}';
    $timestamp = time();

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => "t={$timestamp},te=tampered_signature_value,li=",
    ], $payload);

    $middleware->handle($request, fn () => new Response('OK'));
})->throws(AccessDeniedHttpException::class);

it('rejects an expired timestamp', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test', tolerance: 300);
    $payload = '{"type":"test"}';
    $expiredTimestamp = time() - 600;
    $signature = hash_hmac('sha256', $expiredTimestamp.'.'.$payload, 'whsec_test');

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => "t={$expiredTimestamp},te={$signature},li=",
    ], $payload);

    $middleware->handle($request, fn () => new Response('OK'));
})->throws(AccessDeniedHttpException::class);

it('accepts a valid signature with tolerance zero regardless of timestamp', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test', tolerance: 0);
    $payload = '{"type":"test"}';
    $oldTimestamp = 1000000;
    $signature = hash_hmac('sha256', $oldTimestamp.'.'.$payload, 'whsec_test');

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => "t={$oldTimestamp},te={$signature},li=",
    ], $payload);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

it('accepts a valid test signature', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test');
    $payload = '{"type":"test"}';
    $header = buildSignatureHeader($payload, 'whsec_test');

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => $header,
    ], $payload);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

it('accepts a valid live signature', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_live');
    $payload = '{"type":"live_event"}';
    $header = buildSignatureHeader($payload, 'whsec_live', useLive: true);

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_PAYREX_SIGNATURE' => $header,
    ], $payload);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

it('rejects when no signature header is present', function () {
    $middleware = new VerifyWebhookSignature(webhookSecret: 'whsec_test');

    $request = Request::create('/webhook', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], '{"type":"test"}');

    $middleware->handle($request, fn () => new Response('OK'));
})->throws(AccessDeniedHttpException::class, 'Missing Payrex-Signature header.');
