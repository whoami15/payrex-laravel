<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when funds are available in a cash balance. */
final class CashBalanceFundsAvailable extends PayrexEvent {}
