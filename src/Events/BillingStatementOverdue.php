<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched 5 days after a billing statement becomes past due. */
final class BillingStatementOverdue extends PayrexEvent {}
