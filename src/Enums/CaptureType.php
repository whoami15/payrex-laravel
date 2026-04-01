<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum CaptureType: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';
}
