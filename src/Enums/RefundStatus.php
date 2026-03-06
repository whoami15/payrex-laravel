<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum RefundStatus: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Pending = 'pending';
}
