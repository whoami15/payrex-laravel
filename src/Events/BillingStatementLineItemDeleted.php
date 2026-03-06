<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a billing statement line item is deleted. */
final class BillingStatementLineItemDeleted extends PayrexEvent {}
