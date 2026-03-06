<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum CheckoutSessionStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Expired = 'expired';
}
