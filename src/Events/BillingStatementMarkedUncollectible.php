<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a billing statement is marked as uncollectible. */
final class BillingStatementMarkedUncollectible extends PayrexEvent {}
