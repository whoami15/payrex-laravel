# Changelog

All notable changes to `payrex-laravel` will be documented in this file.

## [Unreleased](https://github.com/whoami15/payrex-laravel/compare/v1.0.1...main)

## [v1.0.1](https://github.com/whoami15/payrex-laravel/compare/v1.0.0...v1.0.1) - 2026-03-21

### Fixed

* `PayrexObject::$id` is now nullable to handle partial API expansions where the expanded object is missing its `id` field (e.g., `checkout_session.customer` returns customer data without `id`)
* `expandRelation()` no longer crashes when the API returns an expanded relation without an `id` — it constructs the DTO with a `null` id instead

## [v1.0.0](https://github.com/whoami15/payrex-laravel/compare/v1.0.0-beta1...v1.0.0) - 2026-03-18

* `Payrex` facade for static access to all client methods
* `PaymentIntentResource` with `create()`, `retrieve()`, `cancel()`, `capture()`
* `PaymentResource` with `retrieve()`, `update()`
* `RefundResource` with `create()`, `update()`
* `CustomerResource` with `create()`, `list()`, `retrieve()`, `update()`, `delete()`
* `CheckoutSessionResource` with `create()`, `retrieve()`, `expire()`
* `BillingStatementResource` with `create()`, `list()`, `retrieve()`, `update()`, `delete()`, `finalize()`, `void()`, `markUncollectible()`, `send()`
* `BillingStatementLineItemResource` with `create()`, `update()`, `delete()`
* `PayoutTransactionResource` with `list()` scoped to a payout
* `WebhookResource` with `create()`, `list()`, `retrieve()`, `update()`, `delete()`, `enable()`, `disable()`
* `PayrexCollection` with cursor-based `autoPaginate()` returning `LazyCollection`
* `WebhookController` with `VerifyWebhookSignature` middleware
* `constructEvent()` for custom webhook handling
* Conditional webhook route registration via `PAYREX_WEBHOOK_ENABLED`
* `payrex:webhook-list`, `payrex:webhook-create`, `payrex:webhook-update`, `payrex:webhook-delete`, `payrex:webhook-toggle`, `payrex:webhook-test` artisan commands
* `HasPayrexCustomer` trait for linking Eloquent models to PayRex customers
* Publishable migration for `payrex_customer_id` column
* Default currency auto-applied on resource methods that accept `currency`
* `getLastResponse()` for API response metadata
* Configurable HTTP timeouts and retry for 5xx errors
* Laravel 13 support
