<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a billing statement is sent to the customer. */
final class BillingStatementSent extends PayrexEvent {}
