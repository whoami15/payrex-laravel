<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

use Illuminate\Foundation\Events\Dispatchable;
use LegionHQ\LaravelPayrex\Data\PayrexObject;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Exceptions\PayrexException;

abstract class PayrexEvent
{
    use Dispatchable;

    /**
     * Create a new PayRex event instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {}

    /**
     * @internal Use PayrexClient::constructEvent() instead.
     *
     * @param  array<string, mixed>  $data
     */
    public static function constructFrom(array $data): self
    {
        $eventClass = self::resolveEventClass($data['type'] ?? '');

        if ($eventClass) {
            return new $eventClass($data);
        }

        return new WebhookReceived($data);
    }

    /**
     * Dispatch webhook events from a raw payload.
     *
     * Dispatch order:
     * 1. WebhookReceived is always dispatched first (catch-all).
     * 2. If the payload type maps to a known event class, that typed event is dispatched second.
     *
     * Both dispatches are synchronous. If any listener throws an exception, subsequent
     * dispatches are halted and the exception propagates to the caller (typically the
     * WebhookController), allowing the webhook delivery to fail and be retried by PayRex.
     *
     * @internal Used by WebhookController.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function dispatchWebhook(array $payload): void
    {
        WebhookReceived::dispatch($payload);

        $eventType = $payload['type'] ?? null;

        if ($eventType) {
            $eventClass = self::resolveEventClass($eventType);

            if ($eventClass) {
                $eventClass::dispatch($payload);
            }
        }
    }

    /**
     * @internal
     *
     * @return class-string<PayrexEvent>|null
     */
    protected static function resolveEventClass(string $eventType): ?string
    {
        return WebhookEventType::tryFrom($eventType)?->eventClass();
    }

    /**
     * The affected resource as a typed DTO with enum casting.
     */
    public function data(): PayrexObject
    {
        $data = $this->payload['data'] ?? null;

        if (! is_array($data) || $data === []) {
            throw new PayrexException(
                'Webhook payload is missing the expected "data" structure.'
            );
        }

        return PayrexObject::constructFrom($data);
    }

    /**
     * Get the webhook event type as a typed enum.
     */
    public function eventType(): ?WebhookEventType
    {
        $type = $this->payload['type'] ?? null;

        if ($type === null) {
            return null;
        }

        return WebhookEventType::tryFrom($type);
    }

    /**
     * Determine if the event originated from the live environment.
     */
    public function isLiveMode(): bool
    {
        return ($this->payload['livemode'] ?? false) === true;
    }
}
