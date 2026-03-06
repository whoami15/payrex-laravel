# Contributing to PayRex for Laravel

Contributions are welcome and will be fully credited.

## Code of Conduct

The code of conduct is described in [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md).

## Issues

We use GitHub issues to track bugs and feature requests.

Please ensure your description is clear and has sufficient instructions to be able to reproduce the issue.

## Getting Started

Contributing to open-source can be scary. Don't be afraid!
We are looking forward to working together to improve this package!

Here is a small checklist to get you going:

- Discuss the changes you want to make first!
- Create a fork of this repository.
- Clone your own repository.
- Run `just install` to get everything set up for you.
- Checkout a new branch and make the changes you want to make.
- Run `just verify` to verify your code is ok to submit.
- Submit your Pull Request.

> **Note:** If you don't have [just](https://github.com/casey/just) installed, you can use the equivalent `composer` commands directly. See the [available commands](#available-commands) section below.

## Submitting Pull Requests

Before we can merge your Pull Request, here are some guidelines that you need to follow.

These guidelines exist not to annoy you, but to keep the code base clean, unified and future proof.

- **Open an issue first** for significant changes to discuss the approach
- **One pull request per feature** - if you want to do more than one thing, send multiple pull requests
- **Add tests** - your patch won't be accepted if it doesn't have tests
- **Document any changes** - update the README or docs if your PR changes behavior

### Principles

- All files must contain `declare(strict_types=1)`
- All properties should be `readonly`
- All methods must have full type hints (parameters and return types)
- Use string-backed enums for status values
- DTOs must be immutable with static factory constructors

### Tests

PayRex for Laravel maintains 100% code coverage, meaning everything within the package *must* be tested.

If you are submitting a bug fix, please add a test case to reproduce the bug.
If you are submitting a new feature, please make sure to add tests for all possible code paths.

To run the tests, use `just test`.

### Code Style

We use [Laravel Pint](https://laravel.com/docs/pint) with the default Laravel preset.

To check if your code contains any style issues, use `just format-check`.

To fix style issues automatically, use `just format`.

### Static Analysis

PayRex for Laravel uses [PHPStan](https://phpstan.org) (via [Larastan](https://github.com/larastan/larastan)) at level 8 for static analysis.

To ensure that your code doesn't contain any type issues, use `just analyse`.

## Available Commands

| Just | Composer | Description |
|------|----------|-------------|
| `just install` | `composer install` | Install dependencies |
| `just test` | `composer test` | Run test suite |
| `just test-coverage` | `composer test-coverage` | Run tests with coverage |
| `just analyse` | `composer analyse` | Run PHPStan |
| `just format` | `composer format` | Fix code style |
| `just format-check` | `composer format -- --test` | Check code style |
| `just verify` | - | Run format-check + analyse + test |

## Security Disclosures

You can read more about how to report security issues in our [Security Policy](./SECURITY.md).

## License

By contributing to PayRex for Laravel, you agree that your contributions will be licensed under the [MIT License](./LICENSE.md).
