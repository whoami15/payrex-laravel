<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum PaymentIntentStatus: string
{
    case AwaitingPaymentMethod = 'awaiting_payment_method';
    case AwaitingNextAction = 'awaiting_next_action';
    case AwaitingCapture = 'awaiting_capture';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Canceled = 'canceled';
}
