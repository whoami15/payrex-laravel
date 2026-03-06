<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum PayoutStatus: string
{
    case Pending = 'pending';
    case InTransit = 'in_transit';
    case Failed = 'failed';
    case Successful = 'successful';
}
