<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a payment intent is authorized and ready for capture (card hold). */
final class PaymentIntentAmountCapturable extends PayrexEvent {}
