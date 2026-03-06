<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum WebhookEndpointStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
