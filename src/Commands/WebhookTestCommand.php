<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use LegionHQ\LaravelPayrex\Enums\WebhookEventType;
use LegionHQ\LaravelPayrex\Events\PayrexEvent;

final class WebhookTestCommand extends Command
{
    protected $signature = 'payrex:webhook-test {type : The event type to simulate (e.g. payment_intent.succeeded)}';

    protected $description = 'Dispatch a synthetic webhook event locally for testing listeners';

    /** @var array<string, string> */
    protected const RESOURCE_ID_PREFIXES = [
        'payment_intent' => 'pi_test_',
        'payment' => 'pay_test_',
        'refund' => 're_test_',
        'checkout_session' => 'cs_test_',
        'customer' => 'cus_test_',
        'billing_statement' => 'bstm_test_',
        'billing_statement_line_item' => 'bstm_li_test_',
        'payout' => 'po_test_',
        'payout_transaction' => 'po_txn_test_',
        'webhook' => 'wh_test_',
        'cash_balance' => 'cb_test_',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = (string) $this->argument('type');

        $valid = array_column(WebhookEventType::cases(), 'value');

        if (! in_array($type, $valid, true)) {
            $this->components->error("Invalid event type: {$type}");
            $this->components->bulletList($valid);

            return self::FAILURE;
        }

        PayrexEvent::dispatchWebhook($this->buildPayload($type));

        $this->components->info("Dispatched {$type} event successfully.");

        return self::SUCCESS;
    }

    /**
     * Build a synthetic payload that is structurally valid for DTO construction.
     *
     * @return array<string, mixed>
     */
    protected function buildPayload(string $type): array
    {
        $resourceType = Str::before($type, '.');
        $now = time();

        return [
            'id' => 'evt_test_'.Str::random(24),
            'resource' => 'event',
            'type' => $type,
            'livemode' => false,
            'pending_webhooks' => 0,
            'data' => [
                ...$this->resourceData($resourceType, $type),
                'livemode' => false,
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'previous_attributes' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Build resource-specific data fields for the synthetic payload.
     *
     * @return array<string, mixed>
     */
    protected function resourceData(string $resourceType, string $type): array
    {
        $id = $this->syntheticId($resourceType);

        return match ($resourceType) {
            'payment_intent' => [
                'id' => $id,
                'resource' => 'payment_intent',
                'amount' => 10000,
                'amount_received' => $type === 'payment_intent.succeeded' ? 10000 : 0,
                'amount_capturable' => $type === 'payment_intent.amount_capturable' ? 10000 : 0,
                'client_secret' => $id.'_secret_'.Str::random(24),
                'currency' => 'PHP',
                'description' => null,
                'last_payment_error' => null,
                'latest_payment' => null,
                'payment_methods' => ['card'],
                'payment_method_id' => null,
                'payment_method_options' => ['card' => ['capture_type' => 'automatic']],
                'statement_descriptor' => null,
                'status' => Str::after($type, '.'),
                'next_action' => null,
                'return_url' => null,
            ],
            'refund' => [
                'id' => $id,
                'resource' => 'refund',
                'amount' => 10000,
                'currency' => 'PHP',
                'status' => 'succeeded',
                'description' => null,
                'reason' => 'requested_by_customer',
                'remarks' => null,
                'payment_id' => $this->syntheticId('payment'),
            ],
            'checkout_session' => [
                'id' => $id,
                'resource' => 'checkout_session',
                'customer_reference_id' => null,
                'client_secret' => $id.'_secret_'.Str::random(24),
                'status' => 'expired',
                'currency' => 'PHP',
                'line_items' => [
                    [
                        'id' => 'cs_li_test_'.Str::random(24),
                        'resource' => 'checkout_session_line_item',
                        'name' => 'Test Product',
                        'amount' => 10000,
                        'quantity' => 1,
                        'description' => null,
                        'image' => null,
                    ],
                ],
                'url' => 'https://checkout.payrexhq.com/c/test_'.$id,
                'payment_intent' => null,
                'success_url' => 'https://example.com/success',
                'cancel_url' => 'https://example.com/cancel',
                'payment_methods' => ['card'],
                'capture_type' => 'automatic',
                'description' => null,
                'submit_type' => 'pay',
                'expires_at' => time() + 86400,
            ],
            'billing_statement' => $this->billingStatementData($id, $type),
            'billing_statement_line_item' => [
                'id' => $id,
                'resource' => 'billing_statement_line_item',
                'description' => 'Test Line Item',
                'billing_statement_id' => $this->syntheticId('billing_statement'),
                'quantity' => 1,
                'unit_price' => 50000,
            ],
            'payout' => [
                'id' => $id,
                'resource' => 'payout',
                'amount' => 100000,
                'currency' => 'PHP',
                'status' => 'deposited',
            ],
            'cash_balance' => [
                'id' => $id,
                'resource' => 'cash_balance',
                'amount' => 100000,
                'currency' => 'PHP',
                'status' => 'funds_available',
            ],
            default => [
                'id' => $id,
                'resource' => $resourceType,
                'amount' => 10000,
                'currency' => 'PHP',
                'status' => Str::after($type, '.'),
            ],
        };
    }

    /**
     * Build billing statement data with status-appropriate fields.
     *
     * @return array<string, mixed>
     */
    protected function billingStatementData(string $id, string $type): array
    {
        $action = Str::after($type, '.');
        $isDraft = in_array($action, ['created', 'updated', 'deleted'], true);

        return [
            'id' => $id,
            'resource' => 'billing_statement',
            'amount' => 50000,
            'currency' => 'PHP',
            'customer_id' => $this->syntheticId('customer'),
            'description' => 'Test billing statement',
            'due_at' => $isDraft ? 0 : time() + 604800,
            'finalized_at' => $isDraft ? 0 : time(),
            'billing_statement_merchant_name' => null,
            'billing_statement_number' => 'TEST0001-0001',
            'billing_statement_url' => 'https://bill.payrexhq.com/b/test_'.$id,
            'line_items' => [
                [
                    'id' => $this->syntheticId('billing_statement_line_item'),
                    'resource' => 'billing_statement_line_item',
                    'description' => 'Test Line Item',
                    'billing_statement_id' => $id,
                    'livemode' => false,
                    'quantity' => 1,
                    'unit_price' => 50000,
                    'created_at' => time(),
                    'updated_at' => time(),
                ],
            ],
            'statement_descriptor' => null,
            'status' => $this->billingStatementStatus($action),
            'customer' => [
                'id' => $this->syntheticId('customer'),
                'name' => 'Test Customer',
                'email' => 'test@example.com',
            ],
            'payment_intent' => $isDraft ? null : [
                'id' => $this->syntheticId('payment_intent'),
                'resource' => 'payment_intent',
                'amount' => null,
                'amount_received' => null,
                'amount_capturable' => null,
                'client_secret' => null,
                'currency' => null,
                'description' => null,
                'last_payment_error' => null,
                'latest_payment' => null,
                'livemode' => false,
                'metadata' => null,
                'payment_methods' => null,
                'payment_method_id' => null,
                'payment_method_options' => null,
                'statement_descriptor' => null,
                'status' => $action === 'paid' ? 'succeeded' : 'awaiting_payment_method',
                'next_action' => null,
                'return_url' => null,
                'created_at' => time(),
                'updated_at' => time(),
            ],
            'payment_settings' => ['payment_methods' => ['card']],
        ];
    }

    /**
     * Map a billing statement event action to its expected status.
     */
    protected function billingStatementStatus(string $action): string
    {
        return match ($action) {
            'created', 'updated', 'deleted' => 'draft',
            'finalized', 'sent' => 'open',
            'voided' => 'void',
            'marked_uncollectible' => 'uncollectible',
            'paid' => 'paid',
            default => $action,
        };
    }

    /**
     * Generate a synthetic resource ID with the correct prefix.
     */
    protected function syntheticId(string $resourceType): string
    {
        $prefix = self::RESOURCE_ID_PREFIXES[$resourceType] ?? 'res_test_';

        return $prefix.Str::random(24);
    }
}
