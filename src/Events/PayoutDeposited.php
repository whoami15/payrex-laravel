<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when funds are successfully deposited to your bank account. */
final class PayoutDeposited extends PayrexEvent {}
