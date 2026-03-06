<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Middleware;

use Closure;
use Illuminate\Http\Request;
use LegionHQ\LaravelPayrex\Exceptions\WebhookVerificationException;
use LegionHQ\LaravelPayrex\WebhookSignature;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class VerifyWebhookSignature
{
    /**
     * Create a new webhook signature verification middleware instance.
     */
    public function __construct(
        protected readonly string $webhookSecret,
        protected readonly int $tolerance = 300,
    ) {}

    /**
     * Verify the webhook signature and pass the request through.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Payrex-Signature');

        if (! $signature) {
            throw new AccessDeniedHttpException('Missing Payrex-Signature header.');
        }

        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $signature,
                $this->webhookSecret,
                $this->tolerance,
            );
        } catch (WebhookVerificationException $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        return $next($request);
    }
}
