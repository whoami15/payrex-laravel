<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Enums;

use LegionHQ\LaravelPayrex\Events\BillingStatementCreated;
use LegionHQ\LaravelPayrex\Events\BillingStatementDeleted;
use LegionHQ\LaravelPayrex\Events\BillingStatementFinalized;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemCreated;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemDeleted;
use LegionHQ\LaravelPayrex\Events\BillingStatementLineItemUpdated;
use LegionHQ\LaravelPayrex\Events\BillingStatementMarkedUncollectible;
use LegionHQ\LaravelPayrex\Events\BillingStatementOverdue;
use LegionHQ\LaravelPayrex\Events\BillingStatementPaid;
use LegionHQ\LaravelPayrex\Events\BillingStatementSent;
use LegionHQ\LaravelPayrex\Events\BillingStatementUpdated;
use LegionHQ\LaravelPayrex\Events\BillingStatementVoided;
use LegionHQ\LaravelPayrex\Events\BillingStatementWillBeDue;
use LegionHQ\LaravelPayrex\Events\CheckoutSessionExpired;
use LegionHQ\LaravelPayrex\Events\PaymentIntentAwaitingCapture;
use LegionHQ\LaravelPayrex\Events\PaymentIntentSucceeded;
use LegionHQ\LaravelPayrex\Events\PayoutDeposited;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
use LegionHQ\LaravelPayrex\Events\RefundCreated;
use LegionHQ\LaravelPayrex\Events\RefundUpdated;

enum WebhookEventType: string
{
    case PaymentIntentSucceeded = 'payment_intent.succeeded';
    case PaymentIntentAwaitingCapture = 'payment_intent.awaiting_capture';
    case CheckoutSessionExpired = 'checkout_session.expired';
    case PayoutDeposited = 'payout.deposited';
    case RefundCreated = 'refund.created';
    case RefundUpdated = 'refund.updated';
    case BillingStatementCreated = 'billing_statement.created';
    case BillingStatementUpdated = 'billing_statement.updated';
    case BillingStatementDeleted = 'billing_statement.deleted';
    case BillingStatementFinalized = 'billing_statement.finalized';
    case BillingStatementSent = 'billing_statement.sent';
    case BillingStatementMarkedUncollectible = 'billing_statement.marked_uncollectible';
    case BillingStatementVoided = 'billing_statement.voided';
    case BillingStatementPaid = 'billing_statement.paid';
    case BillingStatementWillBeDue = 'billing_statement.will_be_due';
    case BillingStatementOverdue = 'billing_statement.overdue';
    case BillingStatementLineItemCreated = 'billing_statement_line_item.created';
    case BillingStatementLineItemUpdated = 'billing_statement_line_item.updated';
    case BillingStatementLineItemDeleted = 'billing_statement_line_item.deleted';

    /**
     * Get the event class that corresponds to this webhook event type.
     *
     * @return class-string<PayrexEvent>
     */
    public function eventClass(): string
    {
        return match ($this) {
            self::PaymentIntentSucceeded => PaymentIntentSucceeded::class,
            self::PaymentIntentAwaitingCapture => PaymentIntentAwaitingCapture::class,
            self::CheckoutSessionExpired => CheckoutSessionExpired::class,
            self::PayoutDeposited => PayoutDeposited::class,
            self::RefundCreated => RefundCreated::class,
            self::RefundUpdated => RefundUpdated::class,
            self::BillingStatementCreated => BillingStatementCreated::class,
            self::BillingStatementUpdated => BillingStatementUpdated::class,
            self::BillingStatementDeleted => BillingStatementDeleted::class,
            self::BillingStatementFinalized => BillingStatementFinalized::class,
            self::BillingStatementSent => BillingStatementSent::class,
            self::BillingStatementMarkedUncollectible => BillingStatementMarkedUncollectible::class,
            self::BillingStatementVoided => BillingStatementVoided::class,
            self::BillingStatementPaid => BillingStatementPaid::class,
            self::BillingStatementWillBeDue => BillingStatementWillBeDue::class,
            self::BillingStatementOverdue => BillingStatementOverdue::class,
            self::BillingStatementLineItemCreated => BillingStatementLineItemCreated::class,
            self::BillingStatementLineItemUpdated => BillingStatementLineItemUpdated::class,
            self::BillingStatementLineItemDeleted => BillingStatementLineItemDeleted::class,
        };
    }
}
