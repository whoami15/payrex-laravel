<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Events;

/** Dispatched 5 days before a billing statement's due date. */
final class BillingStatementWillBeDue extends PayrexEvent {}
