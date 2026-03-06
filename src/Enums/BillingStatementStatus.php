<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum BillingStatementStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Void = 'void';
    case Uncollectible = 'uncollectible';
}
