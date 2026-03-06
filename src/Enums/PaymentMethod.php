<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case GCash = 'gcash';
    case Maya = 'maya';
    case BillEase = 'billease';
    case QrPh = 'qrph';
    case BdoInstallment = 'bdo_installment';
}
