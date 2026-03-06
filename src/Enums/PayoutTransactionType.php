<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum PayoutTransactionType: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Adjustment = 'adjustment';
}
