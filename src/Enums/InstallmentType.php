<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum InstallmentType: string
{
    case Regular = 'regular';
    case Zero = 'zero';
    case RegularHoliday = 'regular_holiday';
    case ZeroHoliday = 'zero_holiday';
}
