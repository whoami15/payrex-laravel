<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Failed = 'failed';
}
