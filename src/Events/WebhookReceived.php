<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/**
 * Dispatched for every incoming PayRex webhook, regardless of event type.
 * Listen to this if you want a single handler for all webhook events.
 */
final class WebhookReceived extends PayrexEvent {}
