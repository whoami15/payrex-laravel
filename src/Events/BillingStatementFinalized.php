<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched when a billing statement is finalized. */
final class BillingStatementFinalized extends PayrexEvent {}
