<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;

final class WebhookController
{
    /**
     * Handle an incoming PayRex webhook request.
     */
    public function __invoke(Request $request): Response
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        if ($payload === []) {
            return new Response('Empty webhook payload', 400);
        }

        PayrexEvent::dispatchWebhook($payload);

        return new Response('Webhook handled', 200);
    }
}
