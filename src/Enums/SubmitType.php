<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum SubmitType: string
{
    case Pay = 'pay';
    case Donate = 'donate';
}
