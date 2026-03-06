<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Facades;

use Illuminate\Support\Facades\Facade;
use LegionHQ\LaravelPayrex\Data\ApiResponseMetadata;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;
use LegionHQ\LaravelPayrex\PayrexClient;
use LegionHQ\LaravelPayrex\Resources\BillingStatementLineItemResource;
use LegionHQ\LaravelPayrex\Resources\BillingStatementResource;
use LegionHQ\LaravelPayrex\Resources\CheckoutSessionResource;
use LegionHQ\LaravelPayrex\Resources\CustomerResource;
use LegionHQ\LaravelPayrex\Resources\PaymentIntentResource;
use LegionHQ\LaravelPayrex\Resources\PaymentResource;
use LegionHQ\LaravelPayrex\Resources\PayoutTransactionResource;
use LegionHQ\LaravelPayrex\Resources\RefundResource;
use LegionHQ\LaravelPayrex\Resources\WebhookResource;

/**
 * @method static PaymentIntentResource paymentIntents()
 * @method static PaymentResource payments()
 * @method static RefundResource refunds()
 * @method static CustomerResource customers()
 * @method static CheckoutSessionResource checkoutSessions()
 * @method static WebhookResource webhooks()
 * @method static BillingStatementResource billingStatements()
 * @method static BillingStatementLineItemResource billingStatementLineItems()
 * @method static PayoutTransactionResource payoutTransactions()
 * @method static string defaultCurrency()
 * @method static ApiResponseMetadata|null getLastResponse()
 * @method static PayrexEvent constructEvent(string $payload, string $signatureHeader, ?string $secret = null, int $tolerance = 300)
 *
 * @see PayrexClient
 */
class Payrex extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PayrexClient::class;
    }
}
