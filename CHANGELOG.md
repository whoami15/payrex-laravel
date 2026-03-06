# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- PayRex API client with Laravel HTTP client integration
- Facade and dependency injection support
- API resources: PaymentIntents, Payments, Refunds, Customers, CheckoutSessions, Webhooks, BillingStatements, BillingStatementLineItems, Payouts
- Webhook endpoint with signature verification middleware
- Typed event classes for all PayRex webhook event types
- Generic `PayrexWebhookReceived` event for catch-all handling
- PHP backed enums for PaymentMethod, PaymentIntentStatus, PaymentStatus, RefundReason, RefundStatus, CheckoutSessionStatus, BillingStatementStatus, PayoutStatus, WebhookEventType
- Structured exception classes: AuthenticationException, InvalidRequestException, ResourceNotFoundException, PayrexApiException
- Publishable configuration file
