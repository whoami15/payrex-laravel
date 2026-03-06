<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a payment intent completes successfully. */
final class PaymentIntentSucceeded extends PayrexEvent {}
