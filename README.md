# PayRex for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/legionhq/laravel-payrex.svg?style=flat-square)](https://packagist.org/packages/legionhq/laravel-payrex)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/whoami15/payrex-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/whoami15/payrex-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![PHPStan](https://img.shields.io/github/actions/workflow/status/whoami15/payrex-laravel/phpstan.yml?branch=main&label=static%20analysis&style=flat-square)](https://github.com/whoami15/payrex-laravel/actions?query=workflow%3Aphpstan+branch%3Amain)
[![Code Coverage](https://codecov.io/gh/whoami15/payrex-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/whoami15/payrex-laravel)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/whoami15/payrex-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/whoami15/payrex-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/legionhq/laravel-payrex.svg?style=flat-square)](https://packagist.org/packages/legionhq/laravel-payrex)

Unofficial Laravel package for [PayRex](https://payrex.com) payment platform. Easily accept payments via credit/debit cards, GCash, Maya, BillEase, QR Ph and more.

## Documentation

You'll find full documentation on the [docs site](https://payrexlaravel.com).

## Basic Usage

```php
use LegionHQ\LaravelPayrex\Facades\Payrex;

// Create a payment intent
$paymentIntent = Payrex::paymentIntents()->create([
    'amount' => 10000, // ₱100.00 in cents
    'currency' => 'PHP',
    'payment_methods' => ['card', 'gcash', 'maya'],
    'description' => 'Order #1234',
]);

// Create a checkout session
$session = Payrex::checkoutSessions()->create([
    'currency' => 'PHP',
    'line_items' => [
        ['name' => 'Premium Plan', 'amount' => 99900, 'quantity' => 1],
    ],
    'payment_methods' => ['card', 'gcash'],
    'success_url' => route('checkout.success'),
    'cancel_url' => route('checkout.cancel'),
]);

return redirect()->away($session->url);
```

## Installation

You can install the package via composer:

```bash
composer require legionhq/laravel-payrex
```

Publish the config file:

```bash
php artisan vendor:publish --tag="payrex-config"
```

Add your API keys to `.env`:

```env
PAYREX_PUBLIC_KEY=your_public_key
PAYREX_SECRET_KEY=your_secret_key
PAYREX_WEBHOOK_SECRET=your_webhook_secret
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Daryl Legion](https://github.com/whoami15)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
