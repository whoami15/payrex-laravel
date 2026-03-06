<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a checkout session expires. */
final class CheckoutSessionExpired extends PayrexEvent {}
